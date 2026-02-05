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

        // Get all employees (excluding owner)
        $allEmployees = User::where('user_role', '!=', 'owner')->select('user_id as id', 'name')->get();
        $employeeIds = $allEmployees->pluck('id')->toArray();

        // Get attendance stats
        $attendanceStats = DB::table('attendances')
            ->join('shift_schedules', 'attendances.shift_id', '=', 'shift_schedules.shift_id')
            ->whereYear('shift_schedules.shift_date', $year)
            ->whereMonth('shift_schedules.shift_date', $monthNum)
            ->select(
                'attendances.user_id',
                DB::raw('COUNT(CASE WHEN attendances.status = \'on_time\' THEN 1 END) as present'),
                DB::raw('COUNT(CASE WHEN attendances.status = \'late\' THEN 1 END) as late'),
                DB::raw('COUNT(CASE WHEN attendances.status = \'missed\' THEN 1 END) as absent')
            )
            ->whereIn('attendances.user_id', $employeeIds)
            ->groupBy('attendances.user_id')
            ->get()
            ->keyBy('user_id');

        // Build staff attendance array
        $staffAttendance = [];
        $totalPresent = 0;
        $totalLate = 0;
        $totalAbsent = 0;

        foreach ($allEmployees as $employee) {
            $stats = $attendanceStats->get($employee->id);
            $present = $stats->present ?? 0;
            $late = $stats->late ?? 0;
            $absent = $stats->absent ?? 0;

            $staffAttendance[] = [
                'name' => $employee->name,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
            ];

            $totalPresent += $present;
            $totalLate += $late;
            $totalAbsent += $absent;
        }

        // Sort by attendance rate (highest first)
        usort($staffAttendance, function ($a, $b) {
            $totalA = $a['present'] + $a['late'] + $a['absent'];
            $totalB = $b['present'] + $b['late'] + $b['absent'];
            $rateA = $totalA > 0 ? ($a['present'] + $a['late']) / $totalA : 0;
            $rateB = $totalB > 0 ? ($b['present'] + $b['late']) / $totalB : 0;
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