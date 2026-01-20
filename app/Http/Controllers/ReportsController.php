<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest as EmployeeRequest;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->subMonth()->format('Y-m'));
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);
        $staffId = $request->input('staff_id');

        // Get all employees (excluding owner) - just IDs and names
        $allEmployees = User::where('user_role', '!=', 'owner')->select('user_id as id', 'name')->get();

        $employeeIds = $staffId ? [$staffId] : $allEmployees->pluck('id')->toArray();

        // BULK QUERY: Get all attendance stats in one query, filtered by month
        $attendanceStats = DB::table('attendances')
            ->join('shift_schedules', 'attendances.shift_id', '=', 'shift_schedules.shift_id')
            ->whereYear('shift_schedules.shift_date', $year)
            ->whereMonth('shift_schedules.shift_date', $monthNum)
            ->select(
                'attendances.user_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(CASE WHEN attendances.status != \'missed\' THEN 1 END) as present'),
                DB::raw('COUNT(CASE WHEN attendances.status = \'missed\' THEN 1 END) as absent'),
                DB::raw('COUNT(CASE WHEN attendances.status = \'on_time\' THEN 1 END) as on_time'),
                DB::raw('COUNT(CASE WHEN attendances.status = \'late\' THEN 1 END) as late')
            )
            ->whereIn('attendances.user_id', $employeeIds)
            ->groupBy('attendances.user_id')
            ->get()
            ->keyBy('user_id');

        // Compute useful metrics for owners
        $monthStart = Carbon::createFromDate((int) $year, (int) $monthNum, 1)->startOfMonth();
        $monthEnd = Carbon::createFromDate((int) $year, (int) $monthNum, 1)->endOfMonth();

        // 1) Working hours per staff (sum of daily hours for present/late records with both times)
        $attendanceWithTimes = Attendance::with('schedule')
            ->whereIn('user_id', $employeeIds)
            ->whereNotNull('clock_in_time')
            ->whereNotNull('clock_out_time')
            ->whereHas('schedule', function ($q) use ($year, $monthNum) {
                $q->whereYear('shift_date', $year)
                    ->whereMonth('shift_date', $monthNum);
            })
            ->get();
        $secondsByEmp = [];
        $daysWithTimesByEmp = [];
        foreach ($attendanceWithTimes as $att) {
            $in = Carbon::parse($att->clock_in_time);
            $out = Carbon::parse($att->clock_out_time);
            $diff = max(0, $out->diffInSeconds($in, false));
            $secondsByEmp[$att->user_id] = ($secondsByEmp[$att->user_id] ?? 0) + $diff;
            $daysWithTimesByEmp[$att->user_id] = ($daysWithTimesByEmp[$att->user_id] ?? 0) + 1;
        }

        // 2) Approved leaves per staff (status = 1 assumed approved)
        $approvedLeaves = EmployeeRequest::whereIn('user_id', $employeeIds)
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $monthNum)
            ->where('status', 1)
            ->select('user_id', DB::raw('COUNT(*) as approved'))
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        // Calculate summary stats
        $totalEmployees = $allEmployees->count();
        $totalPresent = $attendanceStats->sum('present');
        $totalAbsent = $attendanceStats->sum('absent');
        $totalRecords = $totalPresent + $totalAbsent;
        $attendanceRate = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100, 1) : 0;

        // Calculate average daily hours across all staff with recorded times
        $totalSecondsAll = array_sum($secondsByEmp);
        $totalDaysAll = array_sum($daysWithTimesByEmp);
        $avgDailyHours = $totalDaysAll > 0 ? round(($totalSecondsAll / 3600) / $totalDaysAll, 1) : 0;

        // Find top performer
        $topPerformer = ['name' => 'No Data', 'attendance_rate' => 0];
        $highestRate = 0;
        foreach ($attendanceStats as $empId => $stats) {
            $rate = $stats->total > 0 ? ($stats->present / $stats->total) * 100 : 0;
            if ($rate > $highestRate) {
                $highestRate = $rate;
                $employee = $allEmployees->firstWhere('id', $empId);
                $topPerformer = [
                    'name' => $employee->name ?? 'Unknown',
                    'attendance_rate' => round($rate, 1)
                ];
            }
        }

        $summary = [
            ['label' => 'Employees', 'value' => $totalEmployees],
            ['label' => 'Attendance Rate', 'value' => $attendanceRate . '%'],
            ['label' => 'Avg Daily Hours', 'value' => $avgDailyHours . 'h'],
            ['label' => 'Top Performer', 'value' => $topPerformer['name'] . ' (' . $topPerformer['attendance_rate'] . '%)'],
        ];

        // Build staff arrays
        $employeesToDisplay = $staffId ? $allEmployees->where('id', $staffId) : $allEmployees;

        $staffAttendance = [];
        $staffTasks = [];
        $staffRanking = [];
        $staffHours = [];
        $staffLeaves = [];

        foreach ($employeesToDisplay as $employee) {
            $empId = $employee->id;
            $attendStats = $attendanceStats->get($empId);


            // Attendance breakdown
            $staffAttendance[] = [
                'name' => $employee->name,
                'present' => $attendStats->present ?? 0,
                'absent' => $attendStats->absent ?? 0,
                'late' => $attendStats->late ?? 0,
                'on_time' => $attendStats->on_time ?? 0,
            ];

            // Working hours and approved leaves
            $totalSeconds = $secondsByEmp[$empId] ?? 0;
            $daysCount = $daysWithTimesByEmp[$empId] ?? 0;
            $totalHours = round($totalSeconds / 3600, 1);
            $avgDailyHours = $daysCount > 0 ? round(($totalSeconds / 3600) / $daysCount, 1) : 0.0;
            $staffHours[] = [
                'name' => $employee->name,
                'totalHours' => $totalHours,
                'avgDailyHours' => $avgDailyHours,
            ];

            $staffLeaves[] = [
                'name' => $employee->name,
                'approved' => $approvedLeaves->get($empId)->approved ?? 0,
            ];

            // Ranking
            $attendanceRate = ($attendStats->total ?? 0) > 0 ? round(($attendStats->present / $attendStats->total) * 100, 1) : 0;
            $overallScore = ($attendanceRate * 0.7);

            $staffRanking[] = [
                'name' => $employee->name,
                'attendance' => $attendanceRate,
                'score' => round($overallScore, 1),
            ];
        }

        // Sort ranking by score
        usort($staffRanking, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return Inertia::render('Reports/Reports', [
            'summary' => $summary,
            'staffAttendance' => $staffAttendance,
            'staffTasks' => [],
            'staffHours' => $staffHours,
            'staffLeaves' => $staffLeaves,
            'staffRanking' => $staffRanking,
            'month' => $month,
            'allStaff' => $allEmployees,
            'selectedStaffId' => $staffId,
        ]);
    }

}