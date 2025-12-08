<?php

namespace App\Http\Controllers;

use App\Mail\EmployeeRegisterationCredentials;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    protected array $validationMessages = [
        'phone' => 'This phone number format is not valid.',
        'role' => 'Invalid Role',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortDir = 'asc';
        if ($request->has('sort')) {
            $request->validate([
                'sort' => 'in:user_id,name',
                'sort_dir' => 'required|boolean',
            ]);
            $sortDir = $request->sort_dir ? 'asc' : 'desc';
        }

        $employees = User::where('user_role', '!=', 'owner')
            ->when($request->term, function ($query, $term) {
                $query->where('name', 'ILIKE', '%' . $term . '%')
                    ->orWhere('email', 'ILIKE', '%' . $term . '%')
                    ->orWhere('user_id', 'ILIKE', '%' . $term . '%')
                    ->orWhere('phone', 'ILIKE', '%' . $term . '%')
                    ->orWhere('ic_number', 'ILIKE', '%' . $term . '%');
            })
            ->orderBy($request->sort ?? 'user_id', $sortDir)
            ->select(['user_id as id', 'name', 'email', 'phone', 'ic_number as national_id'])
            ->paginate(config('constants.data.pagination_count'));

        return Inertia::render('Employee/Employees', [
            'employees' => $employees,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Employee/EmployeeCreate', [
            'roles' => [['name' => 'admin'], ['name' => 'employee']],
            'shifts' => [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $res = $request->validate([
            'name' => ['required', 'unique:users', 'max:50'],
            'email' => ['required', 'unique:users', 'email:strict'],
            'ic_number' => ['required', 'unique:users'],
            'phone' => ['required', 'regex:/(^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$)/', 'unique:users'],
            'hired_on' => ['nullable', 'date_format:Y-m-d'],
            'address' => ['required', 'string', 'max:255'],
            'shift_id' => ['nullable', 'integer'],
            'role' => ['required', Rule::in(['admin', 'employee'])],
        ], $this->validationMessages);

        // Create employee
        if (is_null($res['hired_on'])) {
            $res['hired_on'] = now()->format('Y-m-d');
        }

        $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

        $emp = User::create([
            'name' => $res['name'],
            'email' => $res['email'],
            'phone' => $res['phone'],
            'ic_number' => $res['ic_number'],
            'address' => $res['address'],
            'hired_on' => $res['hired_on'],
            'password' => bcrypt($password),
            'user_role' => $res['role'],
        ]);

        // Send email with credentials
        Mail::to($emp->email)->send(new EmployeeRegisterationCredentials([
            'name' => $emp->name,
            'email' => $emp->email,
            'password' => $password,
        ]));

        return to_route('employees.show', ['employee' => $emp->user_id]);
    }

    /**
     * Show current user's profile.
     */
    public function showMyProfile()
    {
        return $this->show(auth()->user()->user_id);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): Response
    {
        return Inertia::render('Employee/EmployeeView', [
            'employee' => User::where('users.user_id', $id)
                ->select(
                    'users.user_id as id',
                    'users.name',
                    'users.phone',
                    'users.ic_number as national_id',
                    'users.email',
                    'users.address',
                    'users.hired_on',
                    'users.user_role'
                )
                ->first(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return Inertia::render('Employee/EmployeeEdit', [
            'employee' => User::where('users.user_id', $id)
                ->select(
                    'users.user_id as id',
                    'users.name',
                    'users.phone',
                    'users.ic_number as national_id',
                    'users.email',
                    'users.address',
                    'users.hired_on',
                    'users.user_role'
                )
                ->first(),
            'roles' => [['name' => 'admin'], ['name' => 'employee']],
            'shifts' => [],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $employee = User::findOrFail($id);

        // Validate input
        $res = $request->validate([
            'name' => ['required', 'unique:users,name,' . $id . ',user_id', 'max:50'],
            'email' => ['required', 'unique:users,email,' . $id . ',user_id', 'email:strict'],
            'ic_number' => ['required', 'unique:users,ic_number,' . $id . ',user_id'],
            'phone' => ['required', 'regex:/(^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$)/', 'unique:users,phone,' . $id . ',user_id'],
            'hired_on' => ['nullable', 'date_format:Y-m-d'],
            'address' => ['required', 'string', 'max:255'],
            'shift_id' => ['nullable', 'integer'],
            'role' => ['required', Rule::in(['admin', 'employee'])],
        ], $this->validationMessages);

        // Update employee
        $employee->update([
            'name' => $res['name'],
            'email' => $res['email'],
            'phone' => $res['phone'],
            'ic_number' => $res['ic_number'],
            'address' => $res['address'],
            'hired_on' => $res['hired_on'],
        ]);

        if (($employee->user_role ?? null) !== $res['role']) {
            $employee->user_role = $res['role'];
            $employee->save();
        }

        return to_route('employees.show', ['employee' => $employee->user_id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $employee = User::findOrFail($id);

        if ($employee->user_id == auth()->user()->user_id) {
            return response()->json(['Error' => 'You cannot delete yourself.'], 403);
        }

        $employee->delete();

        return to_route('employees.index');
    }

    /**
     * Find employees by search term.
     */
    public function find(Request $request)
    {
        return Inertia::render('Employee/EmployeeFind', [
            'employees' => User::when($request->term, function ($query, $term) {
                $query
                    ->where('name', 'ILIKE', '%' . $term . '%')
                    ->orWhere('user_id', 'ILIKE', '%' . $term . '%')
                    ->orWhere('email', 'ILIKE', '%' . $term . '%')
                    ->orWhere('phone', 'ILIKE', '%' . $term . '%')
                    ->orWhere('ic_number', 'ILIKE', '%' . $term . '%');
            })->select(['user_id as id', 'name', 'email', 'phone', 'ic_number as national_id'])->get(),
        ]);
    }
}
