<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Schedule;
use App\Models\LeaveRequest;

class ScheduleController extends Controller
{

    public function admin()
    {
        $staffList = User::where('user_role', '!=', 'owner')->select('user_id as id', 'name')->get();
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

    // Assign a staff to a shift (create or update)
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,user_id',
            'shift_type' => 'required|in:morning,evening',
            'day' => 'required|date', // shift_date
        ]);

        // Prevent assigning if employee has an approved leave overlapping the day
        $hasApprovedLeave = LeaveRequest::where('user_id', $validated['employee_id'])
            ->where('status', 1) // Approved
            ->where('start_date', '<=', $validated['day'])
            ->where(function ($q) use ($validated) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $validated['day']);
            })
            ->exists();
        if ($hasApprovedLeave) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot assign shift: employee has an approved leave on this day.'
            ], 422);
        }

        // Update or create the schedule assignment
        $schedule = Schedule::updateOrCreate(
            [
                'shift_type' => $validated['shift_type'],
                'shift_date' => $validated['day'],
            ],
            [
                'user_id' => $validated['employee_id'],
            ]
        );

        return response()->json(['success' => true, 'schedule' => $schedule]);
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
        $schedules = Schedule::with('employee')
            ->whereBetween('shift_date', [$start->toDateString(), $end])
            ->whereIn('shift_type', ['morning', 'evening'])
            ->get();

        // Format assignments for frontend: { 'YYYY-MM-DD': [morning_employee_id, evening_employee_id] }
        $assignments = [];
        $submitted = false;
        foreach ($schedules as $schedule) {
            $day = $schedule->shift_date;
            if (!isset($assignments[$day])) {
                $assignments[$day] = [null, null];
            }
            $idx = $schedule->shift_type === 'morning' ? 0 : 1;
            $assignments[$day][$idx] = $schedule->user_id;
        }
        return response()->json(['assignments' => $assignments, 'submitted' => $submitted]);
    }

    // Reset all assignments for a given week
    public function reset(Request $request)
    {
        $request->validate([
            'week_start' => 'required|date',
        ]);
        $start = \Carbon\Carbon::parse($request->week_start)->startOfDay();
        $end = (clone $start)->addDays(6)->toDateString();
        \App\Models\Schedule::whereBetween('shift_date', [$start->toDateString(), $end])->delete();
        return response()->json(['success' => true]);
    }



    public function myWeek(Request $request)
    {
        $user = $request->user();
        $weekStart = $request->query('week_start');
        if (!$weekStart) {
            return response()->json(['error' => 'week_start is required'], 400);
        }
        $start = \Carbon\Carbon::parse($weekStart)->startOfDay();
        $end = (clone $start)->addDays(6)->toDateString();
        $schedules = \App\Models\Schedule::whereBetween('shift_date', [$start->toDateString(), $end])
            ->where('user_id', $user->user_id)
            ->get();

        // Format: { 'YYYY-MM-DD': { morning: {start_time, end_time}, evening: {...} } }
        $assignments = [];
        foreach ($schedules as $schedule) {
            $day = $schedule->shift_date;
            $type = $schedule->shift_type; // 'morning' or 'evening'
            if (!isset($assignments[$day])) {
                $assignments[$day] = ['morning' => null, 'evening' => null];
            }
            $assignments[$day][$type] = [
                'name' => ucfirst($type),
                'start_time' => $type === 'morning' ? '06:00' : '15:00',
                'end_time' => $type === 'morning' ? '15:00' : '00:00',
            ];
        }
        return response()->json(['assignments' => $assignments]);
    }



    // Mark a week as submitted
    public function submitWeek(Request $request)
    {
        $request->validate([
            'week_start' => 'required|date',
        ]);
        // submitted flag removed; no-op to keep endpoint stable
        return response()->json(['success' => true]);
    }

    public function day(Request $request)
    {
        $date = $request->query('date');
        if (!$date) {
            return response()->json(['error' => 'date is required'], 400);
        }
        $schedules = \App\Models\Schedule::with('employee')->where('shift_date', $date)->get();
        $assignments = [0 => null, 1 => null, '0_id' => null, '1_id' => null];
        foreach ($schedules as $schedule) {
            $idx = $schedule->shift_type === 'morning' ? 0 : 1;
            $assignments[$idx] = $schedule->employee ? [
                'id' => $schedule->employee->user_id,
                'name' => $schedule->employee->name
            ] : null;
            $assignments[$idx . '_id'] = $schedule->shift_id;
        }
        return response()->json(['assignments' => $assignments]);
    }
}