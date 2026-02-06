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
                    // Owner sees only admin leave requests
                    $q->where('users.user_role', 'admin');
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
                    $q->where('users.user_role', 'admin');
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
            'support_doc' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'], // Max 10MB
        ]);

        $employee = auth()->user();

        // Block if not enough balance
        $balanceField = match ($req['type']) {
            'Annual Leave' => 'annual_leave_balance',
            'Sick Leave' => 'sick_leave_balance',
            'Emergency Leave' => 'emergency_leave_balance',
            default => null,
        };
        if ($balanceField && (($employee->$balanceField ?? 0) < 1)) {
            return back()->withErrors(['leave' => 'Insufficient leave balance for ' . $req['type']]);
        }

        // Create leave request
        $start_date = Carbon::createFromFormat('Y-m-d', $req['date'][0]);
        if ($start_date->isBefore(Carbon::now()) && !$start_date->isSameDay(Carbon::now())) {
            return back()->withErrors(['past_leave' => 'You can\'t make a leave request before today.']);
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

        // Authorization: Owners view admin requests, admins view employee requests
        if (($user->user_role ?? null) === 'owner') {
            if (($leaveRequest->employee->user_role ?? null) !== 'admin') {
                abort(403, 'Owners can only view admin leave requests.');
            }
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

        // Authorization: Owners can only approve admin requests, admins can only approve employee requests
        if (($user->user_role ?? null) === 'owner') {
            // Owner can only approve admin leave requests
            if (($leaveRequest->employee->user_role ?? null) !== 'admin') {
                abort(403, 'Owners can only approve admin leave requests.');
            }
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
                if ($leaveRequest->type === 'Annual Leave' && $targetUser->annual_leave_balance > 0) {
                    $targetUser->annual_leave_balance -= 1;
                    $targetUser->save();
                } elseif ($leaveRequest->type === 'Emergency Leave' && $targetUser->emergency_leave_balance > 0) {
                    $targetUser->emergency_leave_balance -= 1;
                    $targetUser->save();

                    // EMERGENCY LEAVE WORKFLOW: Reassign Check
                    // If employee has shifts during this leave, delete them and prompt supervisor.
                    $reqStart = Carbon::parse($leaveRequest->start_date);
                    $reqEnd = $leaveRequest->end_date ? Carbon::parse($leaveRequest->end_date) : $reqStart;

                    $deletedRows = Schedule::where('user_id', $leaveRequest->user_id)
                        ->whereBetween('shift_date', [$reqStart->format('Y-m-d'), $reqEnd->format('Y-m-d')])
                        ->delete();

                    if ($deletedRows > 0) {
                        return to_route('schedule.admin')->with('reassign_alert', [
                            'name' => $leaveRequest->employee->name,
                            'dates' => $reqStart->format('d M') . ($leaveRequest->end_date ? ' - ' . $reqEnd->format('d M') : ''),
                            'count' => $deletedRows
                        ]);
                    }

                } elseif ($leaveRequest->type === 'Sick Leave' && $targetUser->sick_leave_balance > 0) {
                    $targetUser->sick_leave_balance -= 1;
                    $targetUser->save();
                }
            }
        }
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
