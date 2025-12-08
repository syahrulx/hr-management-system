<?php

namespace App\Http\Controllers;

use App\Mail\RequestStatusUpdated;
use App\Models\LeaveRequest;
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
                    $q->where('users.user_role', 'admin');
                })
                ->select(['leave_requests.request_id as id', 'users.name as employee_name', 'leave_requests.type', 'leave_requests.start_date', 'leave_requests.end_date', 'leave_requests.status', 'leave_requests.remark'])
                ->orderByDesc('leave_requests.request_id')
                ->paginate(10);
        } else {
            $requests = LeaveRequest::query()
                ->where('user_id', $user->user_id)
                ->join('users', 'leave_requests.user_id', '=', 'users.user_id')
                ->select(['leave_requests.request_id as id', 'users.name as employee_name', 'leave_requests.type', 'leave_requests.start_date', 'leave_requests.end_date', 'leave_requests.status', 'leave_requests.remark'])
                ->orderByDesc('leave_requests.request_id')
                ->paginate(10);
        }

        $leaveBalances = null;
        $leaveTotals = null;
        if (in_array(($user->user_role ?? null), ['admin', 'owner'])) {
            $leaveTotals = LeaveRequest::query()
                ->when(($user->user_role ?? null) === 'owner', function ($q) {
                    $q->join('users', 'leave_requests.user_id', '=', 'users.user_id')
                        ->where('users.user_role', 'admin');
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
        // Validate request
        $req = $request->validate([
            'type' => ['required', 'string', 'in:Annual Leave,Emergency Leave,Sick Leave'],
            'date' => ['required', 'array'],
            'date.*' => ['nullable', 'date_format:Y-m-d'],
            'remark' => ['nullable', 'string'],
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

        LeaveRequest::create([
            'type' => $req['type'],
            'start_date' => $req['date'][0],
            'end_date' => $req['date'][1] ?? null,
            'remark' => $req['remark'] ?? null,
            'user_id' => $request->user()->user_id,
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

        if (($user->user_role ?? null) === 'owner') {
            if (($leaveRequest->employee->user_role ?? null) !== 'admin') {
                abort(403);
            }
        } else {
            authenticateIfNotAdmin($user->user_id, $leaveRequest->user_id);
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

        if (($user->user_role ?? null) === 'owner') {
            if (($leaveRequest->employee->user_role ?? null) !== 'admin') {
                abort(403);
            }
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
