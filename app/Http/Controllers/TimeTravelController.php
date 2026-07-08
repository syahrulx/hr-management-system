<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeTravelController extends Controller
{
    /**
     * Guard: time travel must be enabled via config/env, otherwise 404.
     */
    private function ensureEnabled(): void
    {
        abort_unless(config('timetravel.enabled'), 404);
    }

    /**
     * Store a simulated "now" in the session. The TimeTravel middleware applies
     * it on every subsequent request.
     */
    public function set(Request $request)
    {
        $this->ensureEnabled();

        $validated = $request->validate([
            'datetime' => 'required|date',
        ]);

        $request->session()->put(
            'time_travel_now',
            Carbon::parse($validated['datetime'])->toDateTimeString()
        );

        return back();
    }

    /**
     * Clear the simulated time and return to the real clock.
     */
    public function reset(Request $request)
    {
        $this->ensureEnabled();

        $request->session()->forget('time_travel_now');
        Carbon::setTestNow();

        return back();
    }
}
