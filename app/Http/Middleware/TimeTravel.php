<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TimeTravel
{
    /**
     * If the time-travel testing aid is enabled and a fake "now" is stored in
     * the session, freeze the application's clock to that instant WHILE the
     * controller runs, so the real attendance logic sees the simulated time.
     *
     * Crucially, we restore the real clock before the response leaves this
     * middleware. Outer middleware (StartSession etc.) computes the session
     * cookie's expiry from Carbon::now() on the way out — if that saw the fake
     * (e.g. past) time, the cookie would be issued already-expired and the
     * browser would drop it, causing a 419 "Page Expired" on the next request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $applied = false;

        if (config('timetravel.enabled')) {
            $fakeNow = $request->session()->get('time_travel_now');

            if ($fakeNow) {
                try {
                    Carbon::setTestNow(Carbon::parse($fakeNow));
                    $applied = true;
                } catch (\Throwable $e) {
                    // Bad stored value — clear it and fall back to real time.
                    $request->session()->forget('time_travel_now');
                    Carbon::setTestNow();
                }
            } else {
                Carbon::setTestNow(); // ensure real time when nothing is set
            }
        }

        try {
            return $next($request);
        } finally {
            // Restore the real clock so session/cookie housekeeping in the
            // outer middleware is never affected by the simulated time.
            if ($applied) {
                Carbon::setTestNow();
            }
        }
    }
}
