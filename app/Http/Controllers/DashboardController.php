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

        // 2. Calculate "Today's Status" from the collection
        // We look for an attendance where shift_date is today
        $attendanceChecker = $attendancesThisYear->first(function ($att) use ($todayStr) {
            return $att->schedule && $att->schedule->shift_date === $todayStr;
        });

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
                $q->where('shift_date', $today); })
            ->distinct('user_id')->count('user_id');
        $lateToday = Attendance::where('status', 'late')
            ->whereHas('schedule', function ($q) use ($today) {
                $q->where('shift_date', $today); })
            ->distinct('user_id')->count('user_id');
        $absentToday = max($staffCount - $presentToday, 0);
        $pendingRequests = \App\Models\LeaveRequest::where('status', 0)->count();

        return Inertia::render('Dashboard', [
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
            "is_today_off" => false,
            "total_clients" => 0,
            "is_owner" => $isOwner,
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
