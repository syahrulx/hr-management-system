<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Shift/Shifts', [
            'shifts' => Shift::select(['id', 'name', 'start_time', 'end_time', 'shift_payment_multiplier', 'description'])
                ->withCount('employees')
                ->orderBy('id')
                ->paginate(config('constants.data.pagination_count')),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Shift/ShiftCreate');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate shift creation
        $validated = $request->validate([
            'start_time' => ['required', 'array', 'size:3', 'filled'],
            'start_time.hours' => ['required', 'numeric'],
            'start_time.minutes' => ['required', 'numeric'],
            'start_time.seconds' => ['required', 'numeric'],
            'end_time' => ['required', 'array', 'size:3', 'filled'],
            'end_time.hours' => ['required', 'numeric'],
            'end_time.minutes' => ['required', 'numeric'],
            'end_time.seconds' => ['required', 'numeric'],
            'name' => ['required', 'string', 'unique:shifts', 'max:255'],
            'shift_payment_multiplier' => ['nullable', 'numeric', 'min:1'],
            'description' => ['nullable', 'string'],
        ]);

        $startTime = Carbon::createFromTime($request->start_time['hours'], $request->start_time['minutes'], 0)->format('H:i:s');
        $endTime = Carbon::createFromTime($request->end_time['hours'], $request->end_time['minutes'], 0)->format('H:i:s');

        Shift::firstOrCreate([
            'name' => $validated['name'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'shift_payment_multiplier' => $validated['shift_payment_multiplier'],
            'description' => $validated['description'],
        ]);

        return to_route('shifts.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $shift = Shift::withCount("employees")->findOrFail($id);
        $employees = $shift->employees()
            ->where(function ($query) use ($request) {
                $query->where('users.name', 'ILIKE', '%' . $request->term . '%')
                    ->orWhere('users.email', 'ILIKE', '%' . $request->term . '%')
                    ->orWhere('users.user_id', 'ILIKE', '%' . $request->term . '%')
                    ->orWhere('users.phone', 'ILIKE', '%' . $request->term . '%')
                    ->orWhere('users.ic_number', 'ILIKE', '%' . $request->term . '%');
            })
            ->orderBy('users.user_id')
            ->paginate(config('constants.data.pagination_count'), ['users.user_id as id', 'users.name', 'users.phone', 'users.email', 'users.ic_number as national_id']);

        return Inertia::render('Shift/ShiftView', [
            'shift' => $shift,
            'employees' => $employees,
            'searchPar' => $request->term,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $shift = Shift::withCount('employees')->findOrFail($id);
        return Inertia::render('Shift/ShiftEdit', [
            'shift' => $shift,
            'name' => $shift->getRawOriginal('name'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $shift = Shift::findOrFail($id);

        // Handle both array and string time formats
        if (is_array($request->start_time) && is_array($request->end_time)) {
            $validated = $request->validate([
                'start_time' => ['required', 'array', 'size:3', 'filled'],
                'start_time.hours' => ['required', 'numeric'],
                'start_time.minutes' => ['required', 'numeric'],
                'start_time.seconds' => ['required', 'numeric'],
                'end_time' => ['required', 'array', 'size:3', 'filled'],
                'end_time.hours' => ['required', 'numeric'],
                'end_time.minutes' => ['required', 'numeric'],
                'end_time.seconds' => ['required', 'numeric'],
                'name' => ['required', 'string', 'unique:shifts,name,' . $id, 'max:255'],
                'shift_payment_multiplier' => ['nullable', 'numeric', 'min:1'],
                'description' => ['nullable', 'string'],
            ]);

            $validated['start_time'] = Carbon::createFromTime($request->start_time['hours'], $request->start_time['minutes'], 0)->format('H:i:s');
            $validated['end_time'] = Carbon::createFromTime($request->end_time['hours'], $request->end_time['minutes'], 0)->format('H:i:s');
        } else {
            $validated = $request->validate([
                'start_time' => ['required', 'date_format:H:i:s'],
                'end_time' => ['required', 'date_format:H:i:s'],
                'name' => ['required', 'string', 'unique:shifts,name,' . $id, 'max:255'],
                'shift_payment_multiplier' => ['nullable', 'numeric', 'min:1'],
                'description' => ['nullable', 'string'],
            ]);
        }

        $shift->update($validated);

        return to_route('shifts.show', ['shift' => $shift->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Shift::count() === 1) {
            return back()->withErrors(['only_shift' => 'This is the only shift in the system. You cannot delete it.']);
        }

        $shift = Shift::findOrFail($id);

        if ($shift->employees()->count() > 0) {
            foreach ($shift->employees()->get() as $employee) {
                $curShift = $employee->employeeShifts()->whereNull('end_date')->first();
                if ($curShift) {
                    $curShift->update(['shift_id' => Shift::where('id', '!=', $id)->first()->id]);
                }
            }
        }

        $shift->delete();

        return to_route('shifts.index');
    }
}
