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

        // IP Address Restriction (WiFi-specific). See ipAllowed() for the
        // runtime UAT overrides that avoid needing .env changes / redeploys.
        if (!$this->ipAllowed($request)) {
            return redirect()->back()->withErrors(['ip_error' => 'Please make sure you are at the gym and connected with the work WiFi to clock in.']);
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

        // Clock-in availability window margin (minutes) — SRS_VEMS_402:
        // the clock-in is only allowed from 15 minutes before the scheduled
        // start time up until 15 minutes after the scheduled start time.
        $windowMargin = 15;
        $currentTimestamp = Carbon::now();
        $status = 'on_time';

        $schedule = DB::table('shift_schedules')->select('shift_type')->where('shift_id', $scheduleId)->first();
        if ($schedule) {
            if ($schedule->shift_type === 'office') {
                $startStr = '09:00:00';
                $endStr = '17:00:00';
            } else {
                $startStr = $schedule->shift_type === 'morning' ? '06:00:00' : '15:00:00';
                $endStr = $schedule->shift_type === 'morning' ? '15:00:00' : '23:59:59';
            }
            $shiftStart = Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $startStr);
            $shiftEnd = Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $endStr);

            // Too early: more than 15 minutes before the scheduled start time
            if ($currentTimestamp->lt($shiftStart->copy()->subMinutes($windowMargin))) {
                return redirect()->back()->withErrors(['schedule_error' => 'It is too early to clock in. Your shift starts at ' . $shiftStart->format('H:i') . '.']);
            }

            // Too late: the 15-minute clock-in window after the start time has closed
            if ($currentTimestamp->gt($shiftStart->copy()->addMinutes($windowMargin))) {
                return redirect()->back()->withErrors(['schedule_error' => 'The clock-in window has closed. You can only clock in up to ' . $shiftStart->copy()->addMinutes($windowMargin)->format('H:i') . ' (15 minutes after your ' . $shiftStart->format('H:i') . ' shift start).']);
            }

            // Clocking in after the scheduled start time counts as late
            $status = $currentTimestamp->greaterThan($shiftStart) ? 'late' : 'on_time';
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

        // IP Address Restriction (WiFi-specific). See ipAllowed() for the
        // runtime UAT overrides that avoid needing .env changes / redeploys.
        if (!$this->ipAllowed($request)) {
            return redirect()->back()->withErrors(['ip_error' => 'Please make sure you are at the gym and connected with the work WiFi to clock out.']);
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
                        return redirect()->back()->withErrors(['attendance_error' => 'Clock-out window closed (Max 1hr after shift ended). Please contact admin.']);
                    }
                } else {
                    if ($now->gt($shiftEnd->copy()->addHour())) {
                        return redirect()->back()->withErrors(['attendance_error' => 'Clock-out window closed (Max 1hr after shift ended). Please contact admin.']);
                    }
                }

                // If they are clocking out early, update status or log it
                // For now, let's keep the original on_time/late but we could append 'early' if needed
                // Optionally: if ($isEarlyExit) $attendance->status = $attendance->status . '_early';
            }

            $attendance->update([
                "clock_out_time" => Carbon::now(),
            ]);

            return to_route('dashboard.index');
        } else {
            return redirect()->back()->withErrors(['attendance_error' => 'No active Sign-in record was found to sign off.']);
        }
    }

    /**
     * Decide whether the request's IP is allowed to clock in/out.
     *
     * When OFFICE_IP_ADDRESS is set, ONLY that IP (or range) may clock in/out;
     * every other network is blocked. When it is empty, the restriction is off.
     *
     * The allowed value may be a comma-separated list of exact IPs and/or
     * CIDR ranges, e.g. "203.0.113.5, 203.0.113.0/24".
     */
    private function ipAllowed(Request $request): bool
    {
        $allowed = env('OFFICE_IP_ADDRESS');
        if (!$allowed) {
            return true; // nothing configured => unrestricted
        }

        $ip = $request->ip();

        foreach (preg_split('/\s*,\s*/', trim($allowed)) as $entry) {
            if ($entry === '') {
                continue;
            }
            if ($entry === $ip) {
                return true;
            }
            // Preserve original loopback leniency for local dev.
            if ($entry === '127.0.0.1' && $ip === '::1') {
                return true;
            }
            if (str_contains($entry, '/') && $this->ipInCidr($ip, $entry)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether an IPv4 address falls within a CIDR range (e.g. 10.0.0.0/24).
     */
    private function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = array_pad(explode('/', $cidr), 2, null);

        if ($bits === null
            || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false
            || filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }

        $mask = -1 << (32 - (int) $bits);
        return (ip2long($ip) & $mask) === (ip2long($subnet) & $mask);
    }
}
