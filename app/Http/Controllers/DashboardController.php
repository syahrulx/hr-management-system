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
        $attendanceChecker = $user->attendances()
            ->whereHas('schedule', function($q){ $q->where('shift_date', Carbon::today()->toDateString()); })
            ->latest('attendance_id')->first();

        if (is_null($attendanceChecker)) {
            $attendanceStatus = 0;
        } else if ($attendanceChecker->clock_out_time == null) {
            $attendanceStatus = 1;
        } else {
            $attendanceStatus = 2;
        }

        // Simple attendance stats
        $now = Carbon::now();
        $curMonth = $now->month;
        $curYear = $now->year;
        $monthEnd = $now->endOfMonth()->format('j');
        
        $monthAttendance = $user->attendances()
            ->whereHas('schedule', function($q) use($curYear,$curMonth){
                $q->whereYear('shift_date', $curYear)->whereMonth('shift_date', $curMonth);
            })
            ->where('status', '!=', 'missed')
            ->count();
            
        $monthAbsence = $user->attendances()
            ->whereHas('schedule', function($q) use($curYear,$curMonth){
                $q->whereYear('shift_date', $curYear)->whereMonth('shift_date', $curMonth);
            })
            ->where('status', 'missed')
            ->count();

        // Calculate total attendance this year
        $totalAttendanceThisYear = $user->attendances()
            ->whereHas('schedule', function($q) use($curYear){
                $q->whereYear('shift_date', $curYear);
            })
            ->where('status', '!=', 'missed')
            ->count();
            
        $totalAbsenceThisYear = $user->attendances()
            ->whereHas('schedule', function($q) use($curYear){
                $q->whereYear('shift_date', $curYear);
            })
            ->where('status', 'missed')
            ->count();

        // Estimate working days (rough estimate: ~22 working days per month)
        $estimatedWorkingDays = 22;
        $estimatedWeekends = 8;
        $estimatedHolidays = 2;

        // Owner overview metrics
        $isOwner = ($user->user_role ?? null) === 'owner';
        $today = Carbon::today()->toDateString();
        $staffCount = User::whereIn('user_role', ['admin','employee'])->count();
        $presentToday = Attendance::where('status', '!=', 'missed')
            ->whereHas('schedule', function($q) use($today){ $q->where('shift_date', $today); })
            ->distinct('user_id')->count('user_id');
        $lateToday = Attendance::where('status', 'late')
            ->whereHas('schedule', function($q) use($today){ $q->where('shift_date', $today); })
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
