<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Time Travel (UAT clock manipulator)
    |--------------------------------------------------------------------------
    |
    | When enabled, signed-in users can override the system "now" via a small
    | dashboard panel so that time-sensitive flows (attendance clock-in/out
    | windows, lateness, etc.) can be tested without waiting for real time.
    |
    | This is a TESTING aid. Keep it OFF unless you are actively running UAT,
    | and turn it OFF again afterwards by setting TIME_TRAVEL_ENABLED=false.
    |
    */
    'enabled' => (bool) env('TIME_TRAVEL_ENABLED', false),
];
