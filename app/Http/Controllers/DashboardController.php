<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;
use App\Models\Attendance;
use App\Models\User;

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

        // 2. Calculate "Today's Status" - Direct query for today's attendance
        // Using a fresh query to ensure we get the latest data including just-created records
        $attendanceChecker = Attendance::with('schedule')
            ->where('user_id', $user->user_id)
            ->whereHas('schedule', function ($q) use ($todayStr) {
                $q->where('shift_date', $todayStr);
            })
            ->first();

        if (is_null($attendanceChecker)) {
            $attendanceStatus = 0;
        } else if ($attendanceChecker->clock_out_time == null) {
            $attendanceStatus = 1;
        } else {
            $attendanceStatus = 2;
        }

        // 3. Calculate Month Stats in Memory
        $monthAttendance = $attendancesThisYear->filter(function ($att) use ($curMonth) {
            return Carbon::parse($att->schedule->shift_date)->month == $curMonth && $att->status != 'missed';
        })->count();

        $monthAbsence = $attendancesThisYear->filter(function ($att) use ($curMonth) {
            return Carbon::parse($att->schedule->shift_date)->month == $curMonth && $att->status == 'missed';
        })->count();

        // 4. Calculate Year Stats in Memory
        $totalAttendanceThisYear = $attendancesThisYear->filter(function ($att) {
            return $att->status != 'missed';
        })->count();

        $totalAbsenceThisYear = $attendancesThisYear->filter(function ($att) {
            return $att->status == 'missed';
        })->count();

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
        // 5. Chart Data: Last 7 Days Attendance Trends
        // 5. Chart Data: Last 7 Days Attendance Trends (Optimized)
        $chartData = [];
        $chartLabels = [];

        $sevenDaysAgo = Carbon::today()->subDays(6);
        $todayDate = Carbon::today();

        // Single Query to fetch daily counts
        if ($isOwner || ($user->user_role === 'admin')) {
            // Global: Count distinct users present per day
            // We group by the SCHEDULE date.
            $dailyCounts = Attendance::where('status', '!=', 'missed')
                ->join('shift_schedules', 'attendances.shift_id', '=', 'shift_schedules.shift_id')
                ->whereBetween('shift_schedules.shift_date', [$sevenDaysAgo->toDateString(), $todayDate->toDateString()])
                ->selectRaw('shift_schedules.shift_date as date, count(distinct attendances.user_id) as count')
                ->groupBy('shift_schedules.shift_date')
                ->pluck('count', 'date');

        } else {
            // Personal: Count 1 if present
            $dailyCounts = $attendancesThisYear->filter(function ($att) use ($sevenDaysAgo, $todayDate) {
                if (!$att->schedule)
                    return false;
                $shiftDate = Carbon::parse($att->schedule->shift_date);
                return $shiftDate->betweenIncluded($sevenDaysAgo, $todayDate) && $att->status != 'missed';
            })->countBy(function ($att) {
                return $att->schedule->shift_date;
            });
        }

        // Fill in the gaps (0 for days with no attendance)
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $dStr = $d->toDateString();
            $chartLabels[] = $d->format('D');
            $chartData[] = $dailyCounts[$dStr] ?? 0;
        }

        $pendingRequests = \App\Models\LeaveRequest::whereRaw('status = 0')->count();

        // Check if there is a schedule for today
        $hasScheduleToday = $user->schedules()->where('shift_date', $today)->exists();

        return Inertia::render('Dashboard', [
            "chart" => [
                "labels" => $chartLabels,
                "data" => $chartData,
            ],
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
            "sign_in_time" => $attendanceChecker?->clock_in_time,
            "sign_off_time" => $attendanceChecker?->clock_out_time,
            "is_today_off" => false,
            "total_clients" => 0,
            "is_owner" => $isOwner,
            "has_schedule_today" => $hasScheduleToday,
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
