<?php

namespace App\Tasks;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;

class DailyAttendanceHandle
{
    public function __invoke(): void
    {
        logger("Daily Attendance Maintenance is running");
        Artisan::call('down --retry=1 --secret=HelloKittyImSoPretty --render="errors::503_daily"');

        // This will run at 12:00 AM every day, so we need to check yesterday's attendance
        $carbon = CarbonImmutable::now()->subDay();
        $date = $carbon->toDateString();

        // Skip weekend days (Saturday & Sunday) - simplified since Globals deleted
        if (in_array($carbon->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
            logger("Yesterday was a weekend off day, nothing to do in the attendance scheduler");
            Artisan::call('up');
            return;
        }

        // Mark all scheduled employees who did not sign off as missed
        Attendance::whereHas('schedule', function($q) use($date){
                $q->where('shift_date', $date);
            })
            ->whereNull('clock_out_time')
            ->update(['status' => 'missed']);

        // If a user does not have attendance taken that day for their scheduled shifts, create a record and mark it as missed
        $schedules = \App\Models\Schedule::where('shift_date', $date)->get();
        foreach ($schedules as $schedule) {
            // Only create a missed record for scheduled shifts with no attendance
            if (!Attendance::where('shift_id', $schedule->shift_id)->exists()) {
                Attendance::create([
                    'user_id' => $schedule->user_id,
                    'shift_id' => $schedule->shift_id,
                    'status' => 'missed',
                    'clock_in_time' => null,
                    'clock_out_time' => null,
                ]);
            }
        }

        Artisan::call('up');
        logger("DailyAttendanceHandle Completed");
    }
}
