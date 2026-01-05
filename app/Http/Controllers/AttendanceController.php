<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function attendanceDashboard()
    {
        $employee = auth()->user();
        $now = Carbon::now();
        $curMonth = $now->month;
        $curYear = $now->year;

        // Calculate attendance stats for current month
        $monthAttendance = $employee->attendances()
            ->whereHas('schedule', function ($q) use ($curYear, $curMonth) {
                $q->whereYear('shift_date', $curYear)->whereMonth('shift_date', $curMonth);
            })
            ->where('status', '!=', 'missed')
            ->count();

        $monthAbsence = $employee->attendances()
            ->whereHas('schedule', function ($q) use ($curYear, $curMonth) {
                $q->whereYear('shift_date', $curYear)->whereMonth('shift_date', $curMonth);
            })
            ->where('status', 'missed')
            ->count();

        // Calculate total attendance this year
        $totalAttendanceThisYear = $employee->attendances()
            ->whereHas('schedule', function ($q) use ($curYear) {
                $q->whereYear('shift_date', $curYear);
            })
            ->where('status', '!=', 'missed')
            ->count();

        $totalAbsenceThisYear = $employee->attendances()
            ->whereHas('schedule', function ($q) use ($curYear) {
                $q->whereYear('shift_date', $curYear);
            })
            ->where('status', 'missed')
            ->count();

        // Estimate working days (rough estimate: ~22 working days per month)
        $employeeStats = [
            "attendedThisMonth" => $monthAttendance,
            "absentedThisMonth" => $monthAbsence,
            "attendableThisMonth" => 22,
            "weekendsThisMonth" => 8,
            "holidaysThisMonth" => 2,
            "totalAttendanceSoFar" => $totalAttendanceThisYear,
            "totalAbsenceSoFar" => $totalAbsenceThisYear,
            "hoursDifferenceSoFar" => 0,
            "actualHoursThisMonth" => 0,
            "expectedHoursThisMonth" => 0,
            "hoursDifference" => 0,
            "YearStats" => [
                "absence_limit" => 30,
                "workingDaysThisYear" => 250,
                "WeekendOffDaysThisYear" => 104,
                "HolidaysThisYear" => 10,
                "weekendOffDays" => ['Saturday', 'Sunday'],
            ],
        ];

        return Inertia::render('Attendance/AttendanceDashboard', [
            "EmployeeStats" => $employeeStats,
        ]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'term' => 'nullable|date_format:Y-m-d',
        ]);
        $dateParam = $request->input('term', '');

        if ($dateParam) {
            $date = Carbon::createFromFormat('Y-m-d', $dateParam)->startOfDay();
            if ($date->isAfter(Carbon::today())) {
                return response()->json(['Error' => 'Date cannot be in the future. Go back and choose a date before today.']);
            }
            $date = $date->toDateString();
        } else {
            $date = '';
        }

        $attendanceList = Attendance::join('shift_schedules', 'attendances.shift_id', '=', 'shift_schedules.shift_id')
            ->select(
                'shift_schedules.shift_date as date',
                DB::raw('COUNT(CASE WHEN attendances.status IN (\'late\', \'on_time\') THEN 1 END) as total_attendance'),
                DB::raw('COUNT(CASE WHEN attendances.status = \'on_time\' THEN 1 END) as attended_on_time'),
                DB::raw('COUNT(CASE WHEN attendances.status = \'late\' THEN 1 END) as attended_late'),
                DB::raw('COUNT(CASE WHEN attendances.status = \'missed\' THEN 1 END) as absented')
            )
            ->groupBy('attendances.shift_id', 'shift_schedules.shift_date')
            ->orderByDesc('shift_schedules.shift_date');

        if ($date) {
            $attendanceList->where('shift_schedules.shift_date', $date);
        }

        return Inertia::render('Attendance/Attendances', [
            "attendanceList" => $attendanceList->paginate(config('constants.data.pagination_count')),
            "dateParam" => $date,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if ($request->term) {
            $request->validate([
                'term' => 'required|date_format:Y-m-d',
            ]);
            $date = Carbon::createFromFormat('Y-m-d', urldecode($request->term))->startOfDay();
            if ($date->isAfter(Carbon::today())) {
                return response()->json(['message' => 'Date cannot be in the future. Go back and choose a date before today.']);
            }
            $date = $date->toDateString();
        } else {
            $date = Carbon::today()->toDateString();
        }

        $attendanceList = Attendance::with('employee:users.user_id,name', 'schedule')
            ->whereHas('schedule', function ($q) use ($date) {
                $q->where('shift_date', $date);
            })
            ->orderBy('attendance_id')->get();

        return Inertia::render('Attendance/AttendanceCreate', [
            "dateParam" => $request->term ?? Carbon::today()->toDateString(),
            "employees" => User::where('user_role', '!=', 'owner')->select(['user_id as id', 'name'])->where('hired_on', '<=', $date)->orderBy('user_id')->get(),
            "attendances" => $attendanceList,
            "attendable" => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate mass attendance creation
        $rules = [
            'date' => 'required|date',
            'employee_id' => 'required|array',
            'employee_id.*' => 'required|integer',
            'status' => 'required|array',
            'status.*' => 'required|in:on_time,late,missed',
            'sign_in_time' => 'required|array',
            'sign_in_time.*' => 'required|array|size:3',
            'sign_in_time.*.hours' => 'required|integer',
            'sign_in_time.*.minutes' => 'required|integer',
            'sign_in_time.*.seconds' => 'nullable|integer',
            'sign_off_time' => 'required|array',
            'sign_off_time.*' => 'required|array|size:3',
            'sign_off_time.*.hours' => 'required|integer',
            'sign_off_time.*.minutes' => 'required|integer',
            'sign_off_time.*.seconds' => 'required|integer',
            'notes' => 'required|array',
            'notes.*' => 'nullable',
        ];

        $res = $request->validate($rules);

        // Create attendance records
        for ($i = 0; $i < count($res['employee_id']); $i++) {
            $userId = $res['employee_id'][$i];
            $theDay = $res['date'];

            // Block if user has an approved leave on this day
            $hasApprovedLeave = DB::table('leave_requests')
                ->where('user_id', $userId)
                ->where('status', 1)
                ->where('start_date', '<=', $theDay)
                ->where(function ($q) use ($theDay) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $theDay);
                })
                ->exists();
            if ($hasApprovedLeave) {
                return response()->json(['error' => "Cannot record attendance: user has an approved leave on $theDay."], 422);
            }

            // Require a schedule entry for that day
            $scheduleId = DB::table('shift_schedules')
                ->where('user_id', $userId)
                ->where('shift_date', $theDay)
                ->value('shift_id');
            if (!$scheduleId) {
                return response()->json(['error' => "Cannot record attendance: user has no schedule on $theDay."], 422);
            }

            // Update or create attendance record
            $empAtt = User::find($userId)->attendances()->where('shift_id', $scheduleId)->get();
            if (count($empAtt) > 0) {
                $empAtt[0]->update([
                    'status' => $res['status'][$i],
                    'clock_in_time' => Carbon::createFromTime($res['sign_in_time'][$i]['hours'], $res['sign_in_time'][$i]['minutes'], $res['sign_in_time'][$i]['seconds'])->format('H:i:s'),
                    'clock_out_time' => Carbon::createFromTime($res['sign_off_time'][$i]['hours'], $res['sign_off_time'][$i]['minutes'], $res['sign_off_time'][$i]['seconds'])->format('H:i:s'),
                    'shift_id' => $scheduleId,
                ]);
                for ($j = 1; $j < count($empAtt); $j++) {
                    $empAtt[$j]->delete();
                }
                continue;
            }

            Attendance::create([
                'user_id' => $userId,
                'status' => $res['status'][$i],
                'clock_in_time' => Carbon::createFromTime($res['sign_in_time'][$i]['hours'], $res['sign_in_time'][$i]['minutes'], $res['sign_in_time'][$i]['seconds'])->format('H:i:s'),
                'clock_out_time' => Carbon::createFromTime($res['sign_off_time'][$i]['hours'], $res['sign_off_time'][$i]['minutes'], $res['sign_off_time'][$i]['seconds'])->format('H:i:s'),
                'shift_id' => $scheduleId,
            ]);
        }

        return to_route('attendances.index');
    }

    public function dayShow(string $day)
    {
        $validator = Validator::make(['dateParameter' => $day], [
            'dateParameter' => 'required|string|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return response()->json([$validator->errors()]);
        }
        $date = $validator->validated()['dateParameter'];

        $attendanceList = Attendance::with('employee:users.user_id,name', 'schedule')
            ->whereHas('schedule', function ($q) use ($date) {
                $q->where('shift_date', $date);
            })
            ->select(['attendance_id', 'user_id', 'status', 'clock_in_time', 'clock_out_time', 'shift_id'])
            ->orderByDesc('attendance_id')->paginate(config('constants.data.pagination_count'));

        return Inertia::render('Attendance/AttendanceDayView', [
            "attendanceList" => $attendanceList,
            "day" => $date,
        ]);
    }

    public function dayDelete(Request $request)
    {
        $res = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = Carbon::createFromFormat('Y-m-d', urldecode($res['date']))->startOfDay();
        if ($date->isAfter(Carbon::today())) {
            return response()->json(['message' => 'Date cannot be in the future. Go back and choose a date before today.']);
        }
        Attendance::where('date', $res['date'])->delete();

        return to_route('attendances.index');
    }

    /***
     **************** SELF-TAKING ATTENDANCE SECTION ****************
     ***/

    public function dashboardSignInAttendance(Request $request)
    {
        // Validate request
        if ($request->id != auth()->user()->user_id) {
            return redirect()->back()->withErrors([
                'authorization_error' => 'You are not authorized to perform this action.',
            ]);
        }

        $today = Carbon::today()->toDateString();

        // Block if user has approved leave today
        $hasApprovedLeave = DB::table('leave_requests')
            ->where('user_id', $request->id)
            ->where('status', 1)
            ->where('start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->exists();
        if ($hasApprovedLeave) {
            return redirect()->back()->withErrors(['leave' => 'You have an approved leave today. Attendance not allowed.']);
        }

        // Require schedule for today
        $scheduleId = DB::table('shift_schedules')
            ->where('user_id', $request->id)
            ->where('shift_date', $today)
            ->value('shift_id');
        if (!$scheduleId) {
            return redirect()->back()->withErrors(['schedule' => 'You have no schedule today.']);
        }

        // Determine lateness
        $lateMargin = 15;
        $currentTimestamp = Carbon::now();
        $status = 'on_time';

        $schedule = DB::table('shift_schedules')->select('shift_type')->where('shift_id', $scheduleId)->first();
        if ($schedule) {
            $startStr = $schedule->shift_type === 'morning' ? '06:00:00' : '15:00:00';
            $shiftStart = Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $startStr);
            $status = $currentTimestamp->greaterThan($shiftStart->copy()->addMinutes($lateMargin)) ? 'late' : 'on_time';
        }

        Attendance::create([
            'user_id' => $request->id,
            'clock_in_time' => $currentTimestamp->format('H:i:s'),
            'clock_out_time' => null,
            'status' => $status,
            'shift_id' => $scheduleId,
        ]);

        return to_route('dashboard.index');
    }

    public function dashboardSignOffAttendance(Request $request)
    {
        // Validate request
        if ($request->id != auth()->user()->user_id) {
            return redirect()->back()->withErrors([
                'authorization_error' => 'You are not authorized to perform this action.',
            ]);
        }

        // FIX: "Cinderella Bug"
        // Instead of looking for a schedule on "Today", look for the latest OPEN attendance.
        // This handles shifts crossing midnight (e.g. In: Mon 22:00, Out: Tue 06:00).
        $attendance = Attendance::where('user_id', $request->id)
            ->whereNull('clock_out_time')
            ->latest('attendance_id')
            ->first();

        // Sanity check: If the last open attendance is too old (e.g. > 24 hours), it might be a forgotten clock-out.
        // For now, we allow it, or arguably we should auto-close it as 'missed' if it's days old.
        // But for this fix, simply finding the open record is sufficient to solve the immediate bug.

        if ($attendance) {
            $attendance->update([
                "clock_out_time" => Carbon::now(),
            ]);
        } else {
            return response()->json(['Error' => 'No active Sign-in record was found to sign off.'], 400);
        }
    }
}
