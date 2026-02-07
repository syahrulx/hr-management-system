<?php

namespace App\Http\Controllers;

use App\Mail\RequestStatusUpdated;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\User;
use Arr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if (in_array(($user->user_role ?? null), ['admin', 'owner'])) {
            $requests = LeaveRequest::query()
                ->join('users', 'leave_requests.user_id', '=', 'users.user_id')
                ->when(($user->user_role ?? null) === 'owner', function ($q) {
                    // Owner sees ALL leave requests (both admin and employee)
                    // No filter needed
                })
                ->when(($user->user_role ?? null) === 'admin', function ($q) use ($user) {
                    // Admin sees employee leave requests AND their own requests
                    $q->where(function ($query) use ($user) {
                        $query->where('users.user_role', 'employee')
                            ->orWhere('leave_requests.user_id', $user->user_id);
                    });
                })
                ->select(['leave_requests.request_id as id', 'users.name as employee_name', 'users.user_role as employee_role', 'leave_requests.user_id', 'leave_requests.type', 'leave_requests.start_date', 'leave_requests.end_date', 'leave_requests.status', 'leave_requests.remark', 'leave_requests.support_doc'])
                ->orderByDesc('leave_requests.request_id')
                ->paginate(10);
        } else {
            $requests = LeaveRequest::query()
                ->where('leave_requests.user_id', $user->user_id)
                ->join('users', 'leave_requests.user_id', '=', 'users.user_id')
                ->select(['leave_requests.request_id as id', 'users.name as employee_name', 'leave_requests.type', 'leave_requests.start_date', 'leave_requests.end_date', 'leave_requests.status', 'leave_requests.remark', 'leave_requests.support_doc'])
                ->orderByDesc('leave_requests.request_id')
                ->paginate(10);
        }

        $leaveBalances = null;
        $leaveTotals = null;
        if (in_array(($user->user_role ?? null), ['admin', 'owner'])) {
            $leaveTotals = LeaveRequest::query()
                ->join('users', 'leave_requests.user_id', '=', 'users.user_id')
                ->when(($user->user_role ?? null) === 'owner', function ($q) {
                    // Owner sees totals for ALL
                })
                ->when(($user->user_role ?? null) === 'admin', function ($q) {
                    $q->where('users.user_role', 'employee');
                })
                ->where('status', 1)
                ->selectRaw('type, count(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type');
        } else {
            $leaveBalances = collect([
                ['leave_type' => 'Annual Leave', 'balance' => $user->annual_leave_balance],
                ['leave_type' => 'Sick Leave', 'balance' => $user->sick_leave_balance],
                ['leave_type' => 'Emergency Leave', 'balance' => $user->emergency_leave_balance],
            ]);
        }

        return Inertia::render('Request/Requests', [
            'requests' => $requests,
            'leaveBalances' => $leaveBalances,
            'leaveTotals' => $leaveTotals,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $user = auth()->user();
        $leaveBalances = collect([
            ['leave_type' => 'Annual Leave', 'balance' => $user->annual_leave_balance],
            ['leave_type' => 'Sick Leave', 'balance' => $user->sick_leave_balance],
            ['leave_type' => 'Emergency Leave', 'balance' => $user->emergency_leave_balance],
        ]);
        $leaveTypes = ['Annual Leave', 'Emergency Leave', 'Sick Leave'];

        return Inertia::render('Request/RequestCreate', [
            'types' => $leaveTypes,
            'leaveBalances' => $leaveBalances,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Increase memory limit for this request to handle large Base64 encoding
        ini_set('memory_limit', '512M');

        // Validate request
        $req = $request->validate([
            'type' => ['required', 'string', 'in:Annual Leave,Emergency Leave,Sick Leave'],
            'date' => ['required', 'array'],
            'date.*' => ['nullable', 'date_format:Y-m-d'],
            'remark' => ['nullable', 'string'],
            'support_doc' => [
                Rule::requiredIf(fn() => in_array($request->type, ['Sick Leave', 'Emergency Leave'])),
                'nullable', // kept because sometimes it's optional (Annual), logic handled by requiredIf
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240'
            ],
        ]);

        $employee = auth()->user();

        // Calculate leave duration first
        $start_date = Carbon::createFromFormat('Y-m-d', $req['date'][0]);
        $end_date_check = isset($req['date'][1]) ? Carbon::createFromFormat('Y-m-d', $req['date'][1]) : $start_date;
        $duration_days = $start_date->diffInDays($end_date_check) + 1;

        // Block if not enough balance
        $balanceField = match ($req['type']) {
            'Annual Leave' => 'annual_leave_balance',
            'Sick Leave' => 'sick_leave_balance',
            'Emergency Leave' => 'emergency_leave_balance',
            default => null,
        };

        if ($balanceField) {
            $currentBalance = $employee->$balanceField ?? 0;
            if ($currentBalance < $duration_days) {
                return back()->withErrors(['leave' => "Insufficient leave balance for {$req['type']}. Required: {$duration_days}, Available: {$currentBalance}"]);
            }
        }

        // --- Overlap Detection ---
        $overlapExists = LeaveRequest::where('user_id', $employee->user_id)
            ->whereIn('status', [0, 1]) // Pending or Approved
            ->where(function ($query) use ($start_date, $end_date_check) {
                $query->whereBetween('start_date', [$start_date->format('Y-m-d'), $end_date_check->format('Y-m-d')])
                    ->orWhereBetween('end_date', [$start_date->format('Y-m-d'), $end_date_check->format('Y-m-d')])
                    ->orWhere(function ($q) use ($start_date, $end_date_check) {
                        $q->where('start_date', '<=', $start_date->format('Y-m-d'))
                            ->where('end_date', '>=', $end_date_check->format('Y-m-d'));
                    });
            })
            ->exists();

        if ($overlapExists) {
            return back()->withErrors(['past_leave' => 'You already have a pending or approved leave request that overlaps with these dates.']);
        }
        // -------------------------

        // Create leave request
        $start_date = Carbon::createFromFormat('Y-m-d', $req['date'][0]);
        if ($start_date->isBefore(Carbon::now()) && !$start_date->isSameDay(Carbon::now())) {
            return back()->withErrors(['past_leave' => 'You can\'t make a leave request before today.']);
        }

        // Annual Leave Restriction: Must be at least 7 days in advance
        if ($req['type'] === 'Annual Leave' && $start_date->lt(Carbon::now()->addDays(7)->startOfDay())) {
            return back()->withErrors(['past_leave' => 'Annual Leave must be requested at least 7 days in advance.']);
        }

        // Annual Leave Restriction: Max 5 consecutive days
        $end_date_check = isset($req['date'][1]) ? Carbon::createFromFormat('Y-m-d', $req['date'][1]) : $start_date;
        $duration_days = $start_date->diffInDays($end_date_check) + 1;

        if ($req['type'] === 'Annual Leave' && $duration_days > 5) {
            return back()->withErrors(['past_leave' => 'Annual Leave cannot exceed 5 consecutive days.']);
        }

        // Handle file upload (Base64)
        $support_doc_path = null;
        if ($request->hasFile('support_doc')) {
            $file = $request->file('support_doc');
            $mime = $file->getMimeType();
            $content = file_get_contents($file->getRealPath());
            $base64 = base64_encode($content);
            $support_doc_path = "data:$mime;base64,$base64";
        }

        LeaveRequest::create([
            'type' => $req['type'],
            'start_date' => $req['date'][0],
            'end_date' => $req['date'][1] ?? $req['date'][0],
            'remark' => $req['remark'] ?? null,
            'user_id' => $request->user()->user_id,
            'support_doc' => $support_doc_path,
        ]);

        return to_route('requests.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $leaveRequest = LeaveRequest::with('employee')->findOrFail($id);
        $user = auth()->user();

        // Authorization: Owners view all, admins view employee requests
        if (($user->user_role ?? null) === 'owner') {
            // Owner can view everything - pass
        } elseif (($user->user_role ?? null) === 'admin') {
            if (($leaveRequest->employee->user_role ?? null) !== 'employee' && $leaveRequest->user_id !== $user->user_id) {
                abort(403, 'Admins can only view employee leave requests or their own.');
            }
        } else {
            // Employees can only view their own requests
            if ($user->user_id !== $leaveRequest->user_id) {
                abort(403, 'Unauthorized.');
            }
        }

        return Inertia::render('Request/RequestView', [
            'request' => $leaveRequest,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $leaveRequest = LeaveRequest::with('employee')->findOrFail($id);
        $user = auth()->user();

        // Authorization: Owners can approve ALL, admins can only approve employee requests
        if (($user->user_role ?? null) === 'owner') {
            // Owner can approve everything - pass
        } elseif (($user->user_role ?? null) === 'admin') {
            // Admin can only approve employee leave requests (not other admins)
            if (($leaveRequest->employee->user_role ?? null) !== 'employee') {
                abort(403, 'Admins can only approve employee leave requests.');
            }
        } else {
            abort(403, 'Unauthorized.');
        }

        // Validate and update
        $req = $request->validate([
            'status' => ['required', 'integer', 'in:1,2'],
        ]);
        $leaveRequest->update($req);

        // Send email notification
        try {
            Mail::to($leaveRequest->employee->email)->send(new RequestStatusUpdated($leaveRequest));
        } catch (\Exception $e) {
            \Log::error('Mail send failed: ' . $e->getMessage());
        }

        // Deduct balance if approving
        if ($request->input('status') == 1) {
            $targetUser = User::find($leaveRequest->user_id);
            if ($targetUser) {
                // Calculate leave duration
                $start = Carbon::parse($leaveRequest->start_date);
                $end = Carbon::parse($leaveRequest->end_date ?? $leaveRequest->start_date);
                $days = $start->diffInDays($end) + 1;

                if ($leaveRequest->type === 'Annual Leave' && $targetUser->annual_leave_balance >= $days) {
                    $targetUser->annual_leave_balance -= $days;
                    $targetUser->save();
                } elseif ($leaveRequest->type === 'Emergency Leave' && $targetUser->emergency_leave_balance >= $days) {
                    $targetUser->emergency_leave_balance -= $days;
                    $targetUser->save();

                    // EMERGENCY LEAVE WORKFLOW: Reassign Check
                    // If employee has shifts during this leave, delete them and prompt supervisor.
                    $deletedRows = Schedule::where('user_id', $leaveRequest->user_id)
                        ->whereBetween('shift_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                        ->delete();

                    if ($deletedRows > 0) {
                        return to_route('schedule.admin')->with('reassign_alert', [
                            'name' => $leaveRequest->employee->name,
                            'dates' => $start->format('d M') . ($leaveRequest->end_date ? ' - ' . $end->format('d M') : ''),
                            'count' => $deletedRows
                        ]);
                    }
                } elseif ($leaveRequest->type === 'Sick Leave' && $targetUser->sick_leave_balance >= $days) {
                    $targetUser->sick_leave_balance -= $days;
                    $targetUser->save();

                    // SICK LEAVE WORKFLOW: Reassign Check (Same as Emergency)
                    $deletedRows = Schedule::where('user_id', $leaveRequest->user_id)
                        ->whereBetween('shift_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                        ->delete();

                    if ($deletedRows > 0) {
                        return to_route('schedule.admin')->with('reassign_alert', [
                            'name' => $leaveRequest->employee->name,
                            'dates' => $start->format('d M') . ($leaveRequest->end_date ? ' - ' . $end->format('d M') : ''),
                            'count' => $deletedRows
                        ]);
                    }
                }
            }
        }



        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        LeaveRequest::findOrFail($id)->delete();
        return to_route('requests.index');
    }
}
