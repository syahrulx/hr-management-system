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
        $month = $request->input('month', Carbon::now()->subMonth()->format('Y-m'));
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
        foreach ($attendances as $record) {
            $uid = $record->user_id;
            if (!isset($stats[$uid])) {
                $stats[$uid] = [
                    'present' => 0,
                    'late' => 0,
                    'absent' => 0,
                    'late_minutes' => 0,
                    'early_minutes' => 0,
                    'early_count' => 0
                ];
            }

            if ($record->status === 'on_time')
                $stats[$uid]['present']++;
            elseif ($record->status === 'late')
                $stats[$uid]['late']++;
            elseif ($record->status === 'missed')
                $stats[$uid]['absent']++;

            // Calculate Late Minutes
            if ($record->status === 'late' && $record->clock_in_time) {
                $shiftStart = match ($record->shift_type) {
                    'morning' => '06:00:00',
                    'evening' => '15:00:00',
                    'office' => '09:00:00',
                    default => '00:00:00'
                };
                $diff = Carbon::parse($record->clock_in_time)->diffInMinutes(Carbon::parse($shiftStart), false);
                // If diff is negative (clock_in > start), it's late. Use abs.
                if ($diff < 0) {
                    $stats[$uid]['late_minutes'] += abs($diff);
                }
            }

            // Calculate Early Leave Minutes
            if ($record->clock_out_time && $record->clock_out_time != '00:00:00') {
                $shiftEnd = match ($record->shift_type) {
                    'morning' => '15:00:00',
                    'evening' => '00:00:00', // simplistic handling for midnight
                    'office' => '17:00:00',
                    default => '00:00:00'
                };

                // Only if shift end isn't midnight (evening shift handling can be tricky if it ends next day)
                if ($shiftEnd !== '00:00:00') {
                    $diff = Carbon::parse($record->clock_out_time)->diffInMinutes(Carbon::parse($shiftEnd), false);
                    // If diff is positive (clock_out < end), it's early
                    if ($diff > 0) {
                        $stats[$uid]['early_minutes'] += $diff;
                        $stats[$uid]['early_count'] = ($stats[$uid]['early_count'] ?? 0) + 1;
                    }
                }
            }
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