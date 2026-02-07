<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    // Simplified AttendanceController for Dashboard Sign-In/Out only

    // Previous admin methods (index, create, store, etc) removed as per request.


    /***
     **************** SELF-TAKING ATTENDANCE SECTION ****************
     ***/

    public function dashboardSignInAttendance(Request $request)
    {
        // Use the authenticated user directly - no need for ID comparison
        $user = auth()->user();

        // IP Address Restriction
        $allowedIp = env('OFFICE_IP_ADDRESS');
        // If OFFICE_IP_ADDRESS is set, enforce it.
        if ($allowedIp && $request->ip() !== $allowedIp) {
            // For checking in local dev where ::1 might happen
            if (!($allowedIp === '127.0.0.1' && $request->ip() === '::1')) {
                return redirect()->back()->withErrors(['ip_error' => 'Please make sure you are at the gym and connected with the work WiFi to clock in.']);
            }
        }

        $today = Carbon::today()->toDateString();

        // Block if user has approved leave today
        $hasApprovedLeave = DB::table('leave_requests')
            ->where('user_id', $user->user_id)
            ->where('status', 1)
            ->where('start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->exists();
        if ($hasApprovedLeave) {
            return redirect()->back()->withErrors(['day_off' => 'You have an approved leave today. Attendance not allowed.']);
        }

        // Require schedule for today
        $scheduleId = DB::table('shift_schedules')
            ->where('user_id', $user->user_id)
            ->where('shift_date', $today)
            ->value('shift_id');

        if (!$scheduleId) {
            return redirect()->back()->withErrors(['schedule_error' => 'You have no schedule today.']);
        }

        // Determine lateness and windows
        $lateMargin = 15;
        $earlyMargin = 30;
        $currentTimestamp = Carbon::now();
        $status = 'on_time';

        $schedule = DB::table('shift_schedules')->select('shift_type')->where('shift_id', $scheduleId)->first();
        if ($schedule) {
            if ($schedule->shift_type === 'office') {
                $startStr = '09:00:00';
            } else {
                $startStr = $schedule->shift_type === 'morning' ? '06:00:00' : '15:00:00';
            }
            $shiftStart = Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $startStr);

            // 30 minute early window check
            if ($currentTimestamp->lt($shiftStart->copy()->subMinutes($earlyMargin))) {
                return redirect()->back()->withErrors(['schedule_error' => 'It is too early to clock in. Your shift starts at ' . $shiftStart->format('H:i') . '.']);
            }

            $status = $currentTimestamp->greaterThan($shiftStart->copy()->addMinutes($lateMargin)) ? 'late' : 'on_time';
        }

        Attendance::create([
            'user_id' => $user->user_id,
            'clock_in_time' => $currentTimestamp->format('H:i:s'),
            'clock_out_time' => null,
            'status' => $status,
            'shift_id' => $scheduleId,
        ]);

        return to_route('dashboard.index');
    }

    public function dashboardSignOffAttendance(Request $request)
    {
        // Use the authenticated user directly - no need for ID comparison
        $user = auth()->user();

        // IP Address Restriction
        $allowedIp = env('OFFICE_IP_ADDRESS');
        // If OFFICE_IP_ADDRESS is set, enforce it.
        if ($allowedIp && $request->ip() !== $allowedIp) {
            // For checking in local dev where ::1 might happen
            if (!($allowedIp === '127.0.0.1' && $request->ip() === '::1')) {
                return response()->json(['Error' => 'Please make sure you are at the gym and connected with the work WiFi to clock out.'], 400);
            }
        }

        // FIX: "Cinderella Bug"
        // Instead of looking for a schedule on "Today", look for the latest OPEN attendance.
        // This handles shifts crossing midnight (e.g. In: Mon 22:00, Out: Tue 06:00).
        $attendance = Attendance::where('user_id', $user->user_id)
            ->whereNull('clock_out_time')
            ->latest('attendance_id')
            ->first();

        // Sanity check: If the last open attendance is too old (e.g. > 24 hours), it might be a forgotten clock-out.
        // For now, we allow it, or arguably we should auto-close it as 'missed' if it's days old.
        // But for this fix, simply finding the open record is sufficient to solve the immediate bug.

        if ($attendance) {
            // Enforce 1 hour after shift end limit
            $schedule = DB::table('shift_schedules')->where('shift_id', $attendance->shift_id)->first();
            if ($schedule) {
                if ($schedule->shift_type === 'morning') {
                    $endStr = '15:00:00';
                } else if ($schedule->shift_type === 'evening') {
                    $endStr = '23:59:59';
                } else if ($schedule->shift_type === 'office') {
                    $endStr = '17:00:00';
                }

                $shiftEnd = Carbon::parse($schedule->shift_date . ' ' . $endStr);
                $now = Carbon::now();

                // Check for early exit (More than 15 mins before shift ends)
                $isEarlyExit = false;
                if ($schedule->shift_type === 'evening') {
                    // Evening shift usually handled by the 1 AM cap, but if they clock out at 10 PM...
                    $isEarlyExit = $now->lt($shiftEnd); // Special case: evening ends at 23:59:59
                } else {
                    $isEarlyExit = $now->lt($shiftEnd->copy()->subMinutes(15));
                }

                // Special case for evening shift ending at midnight -> 1 AM
                if ($schedule->shift_type === 'evening') {
                    $cap = $shiftEnd->copy()->addSecond()->addHour(); // 1:00 AM
                    if ($now->gt($cap)) {
                        return response()->json(['Error' => 'Clock-out window closed (Max 1hr after shift ended). Please contact admin.'], 400);
                    }
                } else {
                    if ($now->gt($shiftEnd->copy()->addHour())) {
                        return response()->json(['Error' => 'Clock-out window closed (Max 1hr after shift ended). Please contact admin.'], 400);
                    }
                }

                // If they are clocking out early, update status or log it
                // For now, let's keep the original on_time/late but we could append 'early' if needed
                // Optionally: if ($isEarlyExit) $attendance->status = $attendance->status . '_early';
            }

            $attendance->update([
                "clock_out_time" => Carbon::now(),
            ]);
        } else {
            return response()->json(['Error' => 'No active Sign-in record was found to sign off.'], 400);
        }
    }
}
