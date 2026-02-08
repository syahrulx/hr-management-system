<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        // Get employees and supervisors (admins)
        $allEmployees = User::whereIn('user_role', ['employee', 'admin'])->select('user_id as id', 'name')->get();
        $employeeIds = $allEmployees->pluck('id')->toArray();

        // Get attendance records for calculation
        $attendances = DB::table('attendances')
            ->join('shift_schedules', 'attendances.shift_id', '=', 'shift_schedules.shift_id')
            ->whereYear('shift_schedules.shift_date', $year)
            ->whereMonth('shift_schedules.shift_date', $monthNum)
            ->whereIn('attendances.user_id', $employeeIds)
            ->select(
                'attendances.user_id',
                'attendances.status',
                'attendances.clock_in_time',
                'attendances.clock_out_time',
                'shift_schedules.shift_type'
            )
            ->get();

        // Build stats per user
        $stats = [];
        foreach ($allEmployees as $employee) {
            $uid = $employee->id;

            // 1. Count actual attendances (Present/Late)
            $userAttendances = $attendances->where('user_id', $uid);
            $present = 0;
            $late = 0;
            $lateMinutes = 0;
            $earlyCount = 0;
            $earlyMinutes = 0;

            foreach ($userAttendances as $record) {
                if ($record->status === 'on_time')
                    $present++;
                elseif ($record->status === 'late') {
                    $late++;
                    // Calculate Late Minutes
                    if ($record->clock_in_time) {
                        $shiftStart = match ($record->shift_type) {
                            'morning' => '06:00:00',
                            'evening' => '15:00:00',
                            'office' => '09:00:00',
                            default => '00:00:00'
                        };
                        $diff = Carbon::parse($record->clock_in_time)->diffInMinutes(Carbon::parse($shiftStart), false);
                        if ($diff < 0)
                            $lateMinutes += abs($diff);
                    }
                }

                // Calculate Early Leave Minutes
                if ($record->clock_out_time && $record->clock_out_time != '00:00:00') {
                    $shiftEnd = match ($record->shift_type) {
                        'morning' => '15:00:00',
                        'evening' => '23:59:59',
                        'office' => '17:00:00',
                        default => '00:00:00'
                    };

                    $endLimit = Carbon::parse($month . '-01')->setDay(1)->format('Y-m-d') . ' ' . $shiftEnd; // Dummy date for comparison
                    $outTime = Carbon::parse($month . '-01')->setDay(1)->format('Y-m-d') . ' ' . $record->clock_out_time;

                    $diff = Carbon::parse($outTime)->diffInMinutes(Carbon::parse($endLimit), false);

                    // If diff is positive (outTime < endLimit), it's early
                    // 15 minute grace period for early exit
                    if ($diff > 15) {
                        $earlyMinutes += $diff;
                        $earlyCount++;
                    }
                }
            }

            // 2. Count Absences (Assigned shifts with no attendance)
            // Only count past dates
            $today = Carbon::now()->toDateString();
            $absent = DB::table('shift_schedules')
                ->where('user_id', $uid)
                ->whereYear('shift_date', $year)
                ->whereMonth('shift_date', $monthNum)
                ->where('shift_date', '<', $today)
                ->whereNotExists(function ($query) use ($uid) {
                    $query->select(DB::raw(1))
                        ->from('attendances')
                        ->whereColumn('attendances.shift_id', 'shift_schedules.shift_id')
                        ->where('attendances.user_id', $uid);
                })
                ->count();

            $stats[$uid] = [
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'late_minutes' => $lateMinutes,
                'early_minutes' => $earlyMinutes,
                'early_count' => $earlyCount
            ];
        }

        // Build staff attendance array
        $staffAttendance = [];
        $totalPresent = 0;
        $totalLate = 0;
        $totalAbsent = 0;

        foreach ($allEmployees as $employee) {
            $userStats = $stats[$employee->id] ?? ['present' => 0, 'late' => 0, 'absent' => 0, 'late_minutes' => 0, 'early_minutes' => 0];

            $staffAttendance[] = [
                'name' => $employee->name,
                'present' => $userStats['present'],
                'late' => $userStats['late'],
                'absent' => $userStats['absent'],
                'late_minutes' => $userStats['late_minutes'],
                'early_minutes' => $userStats['early_minutes'],
                'early_count' => $userStats['early_count'] ?? 0,
            ];

            $totalPresent += $userStats['present'];
            $totalLate += $userStats['late'];
            $totalAbsent += $userStats['absent'];
        }

        // Sort by attendance rate (highest first)
        usort($staffAttendance, function ($a, $b) {
            $totalA = $a['present'] + $a['late'] + $a['absent'];
            $totalB = $b['present'] + $b['late'] + $b['absent'];
            $rateA = $totalA > 0 ? $a['present'] / $totalA : 0;
            $rateB = $totalB > 0 ? $b['present'] / $totalB : 0;
            return $rateB <=> $rateA;
        });

        return Inertia::render('Reports/Reports', [
            'staffAttendance' => $staffAttendance,
            'month' => $month,
            'totalPresent' => $totalPresent,
            'totalLate' => $totalLate,
            'totalAbsent' => $totalAbsent,
        ]);
    }
}