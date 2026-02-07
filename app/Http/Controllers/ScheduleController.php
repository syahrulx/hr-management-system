<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Schedule;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{

    public function admin()
    {
        $staffList = User::where('user_role', 'employee')->select('user_id as id', 'name')->get();
        // You can also filter by role/active status if needed
        return Inertia::render('Schedule/AdminSchedule', [
            'staffList' => $staffList,
        ]);
    }

    public function employee()
    {
        // Fetch only the logged-in employee's schedule
        return Inertia::render('Schedule/MySchedule');
    }

    /**
     * ULTRA-OPTIMIZED (Round 3.2): Reference Leak Fix + Type Safety
     * One single query for ALL validation + fetching current week state.
     * Fixed the PHP reference leak that was corrupting Night Shifts.
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,user_id',
            'shift_type' => 'required|in:morning,evening',
            'day' => 'required|date',
        ]);

        $employeeId = (int) $validated['employee_id'];
        $shiftType = $validated['shift_type'];
        $shiftDate = $validated['day'];
        $otherShiftType = ($shiftType === 'morning') ? 'evening' : 'morning';

        $carbonDay = \Carbon\Carbon::parse($shiftDate);
        $weekStart = $carbonDay->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
        $weekEnd = $carbonDay->copy()->endOfWeek(\Carbon\Carbon::SUNDAY)->toDateString();
        $adminWeekEnd = $carbonDay->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(4)->toDateString();

        // --- THE BIG ASK: One roundtrip to rule them all ---
        $stats = DB::selectOne("
            SELECT 
                (SELECT user_role FROM users WHERE user_id = ?) as user_role,
                (SELECT COUNT(*) FROM shift_schedules WHERE user_id = ? AND shift_date = ? AND shift_type = ?) as same_day_other_shift,
                (SELECT COUNT(*) FROM shift_schedules WHERE user_id = ? AND shift_date BETWEEN ? AND ? AND shift_date != ?) as week_shift_count,
                (SELECT COUNT(*) FROM leave_requests WHERE user_id = ? AND status = 1 AND ? BETWEEN start_date AND COALESCE(end_date, start_date)) as on_leave,
                (SELECT COUNT(*) FROM shift_schedules WHERE user_id IN (SELECT user_id FROM users WHERE user_role = 'admin') AND shift_date BETWEEN ? AND ?) as automation_already_run,
                (SELECT json_agg(t) FROM (
                    SELECT shift_date, shift_type, user_id 
                    FROM shift_schedules 
                    WHERE shift_date BETWEEN ? AND ? 
                    AND shift_type IN ('morning', 'evening')
                ) t) as week_data
        ", [
            $employeeId,
            $employeeId,
            $shiftDate,
            $otherShiftType,
            $employeeId,
            $weekStart,
            $weekEnd,
            $shiftDate,
            $employeeId,
            $shiftDate,
            $weekStart,
            $adminWeekEnd,
            $weekStart,
            $weekEnd
        ]);

        // Validation logic (In-Memory)
        if ($stats->user_role !== 'employee') {
            return response()->json(['success' => false, 'error' => 'Invalid role: ONLY Operational Employees can be assigned.'], 422);
        }
        if ($stats->same_day_other_shift > 0) {
            return response()->json(['success' => false, 'error' => "Already assigned to $otherShiftType shift today."], 422);
        }
        if ($stats->week_shift_count >= 6) {
            return response()->json(['success' => false, 'error' => 'Reached 6-day work week limit.'], 422);
        }
        if ($stats->on_leave > 0) {
            return response()->json(['success' => false, 'error' => 'Employee has approved leave on this day.'], 422);
        }

        // --- EXECUTION: Single Write Transaction ---
        DB::transaction(function () use ($shiftType, $shiftDate, $employeeId, $stats, $weekStart, $adminWeekEnd) {
            Schedule::updateOrCreate(
                ['shift_type' => $shiftType, 'shift_date' => $shiftDate],
                ['user_id' => $employeeId]
            );

            if ($stats->automation_already_run == 0) {
                $admins = User::where('user_role', 'admin')->get();
                $allAdminLeaves = \App\Models\LeaveRequest::whereIn('user_id', $admins->pluck('user_id'))->where('status', 1)->get()->groupBy('user_id');

                $batchShifts = [];
                foreach ($admins as $admin) {
                    $userLeaves = $allAdminLeaves->get($admin->user_id, collect());
                    for ($i = 0; $i < 5; $i++) {
                        $date = \Carbon\Carbon::parse($weekStart)->addDays($i)->toDateString();
                        $onLeave = $userLeaves->contains(function ($leave) use ($date) {
                            $start = $leave->start_date;
                            $end = $leave->end_date ?? $leave->start_date;
                            return $date >= $start && $date <= $end;
                        });
                        if (!$onLeave) {
                            $batchShifts[] = ['user_id' => (int) $admin->user_id, 'shift_date' => $date, 'shift_type' => 'office'];
                        }
                    }
                }
                if (!empty($batchShifts)) {
                    Schedule::insertOrIgnore($batchShifts);
                }
            }
        });

        // --- PREPARE RESPONSE: Format from aggregated data ---
        $weekData = json_decode($stats->week_data ?? '[]', true);

        // CRITICAL FIX: Reference handling (&item) requires explicit unset to avoid corrupting the last array element
        $found = false;
        foreach ($weekData as &$item) {
            if ($item['shift_date'] === $shiftDate && $item['shift_type'] === $shiftType) {
                $item['user_id'] = $employeeId;
                $found = true;
                break;
            }
        }
        unset($item); // Fix the reference leak

        if (!$found) {
            $weekData[] = ['shift_date' => $shiftDate, 'shift_type' => $shiftType, 'user_id' => $employeeId];
        }

        $formattedAssignments = [];
        foreach ($weekData as $dataItem) {
            $day = $dataItem['shift_date'];
            if (!isset($formattedAssignments[$day])) {
                $formattedAssignments[$day] = [null, null];
            }
            $idx = $dataItem['shift_type'] === 'morning' ? 0 : 1;
            // Always cast user_id to int for frontend consistency
            $formattedAssignments[$day][$idx] = $dataItem['user_id'] !== null ? (int) $dataItem['user_id'] : null;
        }

        return response()->json([
            'success' => true,
            'assignments' => $formattedAssignments
        ]);
    }

    // Fetch all assignments for a given week
    public function week(Request $request)
    {
        $weekStart = $request->query('week_start');
        if (!$weekStart) {
            return response()->json(['error' => 'week_start is required'], 400);
        }
        $start = \Carbon\Carbon::parse($weekStart)->startOfDay();
        $end = (clone $start)->addDays(6)->toDateString();

        // Fetching with integer casting in mind
        $schedules = Schedule::whereBetween('shift_date', [$start->toDateString(), $end])
            ->whereIn('shift_type', ['morning', 'evening'])
            ->get();

        $assignments = [];
        $submitted = false;
        foreach ($schedules as $schedule) {
            $day = $schedule->shift_date;
            if (!isset($assignments[$day])) {
                $assignments[$day] = [null, null];
            }
            $idx = $schedule->shift_type === 'morning' ? 0 : 1;
            $assignments[$day][$idx] = (int) $schedule->user_id;
        }
        return response()->json(['assignments' => $assignments, 'submitted' => $submitted]);
    }

    public function reset(Request $request)
    {
        $request->validate(['week_start' => 'required|date']);
        $start = \Carbon\Carbon::parse($request->week_start)->startOfDay();
        $end = (clone $start)->addDays(6)->toDateString();
        \App\Models\Schedule::whereBetween('shift_date', [$start->toDateString(), $end])->delete();
        return response()->json(['success' => true]);
    }

    public function myWeek(Request $request)
    {
        $user = $request->user();
        $weekStart = $request->query('week_start');
        if (!$weekStart)
            return response()->json(['error' => 'week_start is required'], 400);

        $start = \Carbon\Carbon::parse($weekStart)->startOfDay();
        $end = (clone $start)->addDays(6)->toDateString();
        $schedules = \App\Models\Schedule::whereBetween('shift_date', [$start->toDateString(), $end])
            ->where('user_id', $user->user_id)
            ->get();

        $assignments = [];
        foreach ($schedules as $schedule) {
            $day = $schedule->shift_date;
            $type = $schedule->shift_type;
            if (!isset($assignments[$day]))
                $assignments[$day] = ['morning' => null, 'evening' => null];
            $assignments[$day][$type] = [
                'name' => ucfirst($type),
                'start_time' => $type === 'morning' ? '06:00' : '15:00',
                'end_time' => $type === 'morning' ? '15:00' : '00:00',
            ];
        }
        return response()->json(['assignments' => $assignments]);
    }

    public function submitWeek(Request $request)
    {
        $request->validate(['week_start' => 'required|date']);
        return response()->json(['success' => true]);
    }

    public function day(Request $request)
    {
        $date = $request->query('date');
        if (!$date)
            return response()->json(['error' => 'date is required'], 400);
        $schedules = \App\Models\Schedule::with('employee')->where('shift_date', $date)->get();
        $assignments = [0 => null, 1 => null, '0_id' => null, '1_id' => null];
        foreach ($schedules as $schedule) {
            $idx = $schedule->shift_type === 'morning' ? 0 : 1;
            $assignments[$idx] = $schedule->employee ? ['id' => (int) $schedule->employee->user_id, 'name' => $schedule->employee->name] : null;
            $assignments[$idx . '_id'] = (int) $schedule->shift_id;
        }
        return response()->json(['assignments' => $assignments]);
    }
}