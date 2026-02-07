<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function index()
    {
        // Allow all authenticated users to access dashboard
        $user = auth()->user();
        // Batched Query: Fetch all relevant attendances for this year in one go
        $now = Carbon::now();
        $curMonth = $now->month;
        $curYear = $now->year;
        $todayStr = $now->toDateString();

        // 1. Fetch all attendance records for this user for the current year, eager loading schedule
        $attendancesThisYear = $user->attendances()
            ->with([
                'schedule' => function ($q) use ($curYear) {
                    $q->whereYear('shift_date', $curYear);
                }
            ])
            ->get()
            // Filter out attendances where the schedule doesn't match the year (due to eager loading constraint applying to "with" but not the main query if not careful, though here we filter after)
            // Actually, we need to filter the main result based on the relationship relation.
            ->filter(function ($att) use ($curYear) {
                return $att->schedule && Carbon::parse($att->schedule->shift_date)->year == $curYear;
            });

        // 2. Calculate "Today's Status"
        // First, check for any OPEN attendance (handles shifts crossing midnight)
        $openAttendance = Attendance::with('schedule')
            ->where('user_id', $user->user_id)
            ->whereNull('clock_out_time')
            ->latest('attendance_id')
            ->first();

        // Check if there is a schedule for today
        $schedule = $user->schedules()->where('shift_date', $todayStr)->first();

        // Auto-detect schedule for Supervisors (Admins) on weekdays (Mon-Fri)
        if (!$schedule && $user->user_role === 'admin') {
            $dayOfWeek = Carbon::parse($todayStr)->dayOfWeek;
            // 1 = Monday, 5 = Friday
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                $schedule = (object) [
                    'shift_date' => $todayStr,
                    'shift_type' => 'office'
                ];
            }
        }

        $hasScheduleToday = !is_null($schedule);
        $isTooEarly = false;
        $canClockOut = true;
        $shiftStartTime = null;
        $shiftEndTime = null;

        if ($hasScheduleToday) {
            $startStr = '';
            $endStr = '';
            // Determine shift times
            if ($schedule->shift_type === 'morning') {
                $startStr = '06:00:00';
                $endStr = '15:00:00';
            } else if ($schedule->shift_type === 'evening') {
                $startStr = '15:00:00';
                $endStr = '23:59:59'; // Night shift ends at midnight
            } else if ($schedule->shift_type === 'office') {
                $startStr = '09:00:00';
                $endStr = '17:00:00';
            }

            if ($startStr) {
                // Keep full date-time for internal logic
                $startDateTime = Carbon::parse($todayStr . ' ' . $startStr);
                $endDateTime = Carbon::parse($todayStr . ' ' . $endStr);

                // Pass ONLY the time string to avoid "Invalid Date" on frontend
                $shiftStartTime = $startStr;
                $shiftEndTime = $endStr;

                $now = Carbon::now();

                // Too early if more than 30 mins before start
                $isTooEarly = $now->lt($startDateTime->copy()->subMinutes(30));

                // Can clock out if within 1 hour after end
                if ($schedule->shift_type === 'evening') {
                    $nextDayCap = Carbon::parse($todayStr)->addDay()->setHour(1)->setMinute(0)->setSecond(0);
                    $canClockOut = $now->lt($nextDayCap);
                } else {
                    $canClockOut = $now->lt($endDateTime->copy()->addHour());
                }
            }
        }

        // 2. Calculate "Today's Status"
        $attendanceChecker = Attendance::where('user_id', $user->user_id)
            ->whereNull('clock_out_time')
            ->latest('attendance_id')
            ->first();

        if (is_null($attendanceChecker)) {
            $attendanceStatus = 0;
            $sign_in_time = null;
            $sign_off_time_record = Attendance::where('user_id', $user->user_id)
                ->whereNotNull('clock_out_time')
                ->whereHas('schedule', function ($q) use ($todayStr) {
                    $q->where('shift_date', $todayStr);
                })
                ->latest('attendance_id')
                ->first();

            $sign_off_time = $sign_off_time_record?->clock_out_time;
            if ($sign_off_time) {
                $attendanceStatus = 2; // Completed for today
                $sign_in_time = $sign_off_time_record->clock_in_time; // Include sign in time for completed shifts
            }
        } else {
            $attendanceStatus = 1;
            $sign_in_time = $attendanceChecker->clock_in_time;
            $sign_off_time = null;
        }

        // Override canClockOut logic: If they are working, we check the limit.
        // If they aren't signed in, canClockOut is irrelevant.

        // 3. Calculate Month Stats in Memory
        // Re-calculate absences: Count schedules for this month (up to yesterday) with no attendance
        $todayStr = $now->toDateString();
        $monthAbsence = $user->schedules()
            ->whereYear('shift_date', $curYear)
            ->whereMonth('shift_date', $curMonth)
            ->where('shift_date', '<', $todayStr)
            ->whereNotExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('attendances')
                    ->whereColumn('attendances.shift_id', 'shift_schedules.shift_id')
                    ->where('attendances.user_id', $user->user_id);
            })
            ->count();

        $monthAttendance = $attendancesThisYear->filter(function ($att) use ($curMonth) {
            return Carbon::parse($att->schedule->shift_date)->month == $curMonth && $att->status != 'missed';
        })->count();

        // 4. Calculate Year Stats in Memory
        $totalAttendanceThisYear = $attendancesThisYear->filter(function ($att) {
            return $att->status != 'missed';
        })->count();

        $totalAbsenceThisYear = $user->schedules()
            ->whereYear('shift_date', $curYear)
            ->where('shift_date', '<', $todayStr)
            ->whereNotExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('attendances')
                    ->whereColumn('attendances.shift_id', 'shift_schedules.shift_id')
                    ->where('attendances.user_id', $user->user_id);
            })
            ->count();

        // Estimate working days (rough estimate: ~22 working days per month)
        $estimatedWorkingDays = 22;
        $estimatedWeekends = 8;
        $estimatedHolidays = 2;

        // Owner overview metrics
        $isOwner = ($user->user_role ?? null) === 'owner';
        $today = Carbon::today()->toDateString();
        $staffCount = User::whereIn('user_role', ['admin', 'employee'])->count();
        $presentToday = Attendance::where('status', '!=', 'missed')
            ->whereHas('schedule', function ($q) use ($today) {
                $q->where('shift_date', $today);
            })
            ->distinct('user_id')->count('user_id');
        $lateToday = Attendance::where('status', 'late')
            ->whereHas('schedule', function ($q) use ($today) {
                $q->where('shift_date', $today);
            })
            ->distinct('user_id')->count('user_id');
        $absentToday = max($staffCount - $presentToday, 0);
        // Chart Data removed as per requirement

        $pendingRequests = \App\Models\LeaveRequest::whereRaw('status = 0')->count();

        // Leave Balances (Order: Annual, Sick, Emergency)
        // Default max balances - can be adjusted based on company policy
        $leaveBalances = collect([
            ['leave_type' => 'Annual Leave', 'balance' => $user->annual_leave_balance ?? 0, 'total' => 14],
            ['leave_type' => 'Sick Leave', 'balance' => $user->sick_leave_balance ?? 0, 'total' => 14],
            ['leave_type' => 'Emergency Leave', 'balance' => $user->emergency_leave_balance ?? 0, 'total' => 7],
        ]);

        return Inertia::render('Dashboard', [
            "leaveBalances" => $leaveBalances,
            "employee_stats" => [
                "attendedThisMonth" => $monthAttendance,
                "absentedThisMonth" => $monthAbsence,
                "attendableThisMonth" => $estimatedWorkingDays,
                "weekendsThisMonth" => $estimatedWeekends,
                "holidaysThisMonth" => $estimatedHolidays,
                "totalAttendanceSoFar" => $totalAttendanceThisYear,
                "totalAbsenceSoFar" => $totalAbsenceThisYear,
                "hoursDifferenceSoFar" => 0, // Removed feature
                "YearStats" => [
                    "absence_limit" => 30, // Default limit
                ],
            ],
            "attendance_status" => $attendanceStatus,
            "sign_in_time" => $sign_in_time,
            "sign_off_time" => $sign_off_time,
            "is_today_off" => false,
            "total_clients" => 0,
            "is_owner" => $isOwner,
            "has_schedule_today" => $hasScheduleToday,
            "is_too_early" => $isTooEarly,
            "can_clock_out" => $canClockOut,
            "shift_start_time" => $shiftStartTime,
            "owner_stats" => [
                'staffCount' => $staffCount,
                'presentToday' => $presentToday,
                'lateToday' => $lateToday,
                'absentToday' => $absentToday,
                'pendingRequests' => $pendingRequests,
            ],
        ]);
    }

}
