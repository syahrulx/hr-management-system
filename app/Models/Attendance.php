<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'attendance_id';
    protected $casts = [
        'clock_in_time' => 'string',
        'clock_out_time' => 'string',
    ];

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schedule(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'shift_id');
    }

    public function on_time(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class)->where('status', 'on_time');
    }

    /**
     * Find the user's currently-active open attendance (clocked in, not yet out).
     *
     * A "forgotten" clock-out from an earlier shift — one whose clock-out grace
     * window has already closed — is NOT treated as active. This prevents a
     * stale record (e.g. an old morning shift that was never closed) from making
     * the user look like they are still clocked in on a later day/shift.
     */
    public static function activeOpenFor($userId)
    {
        $open = static::with('schedule')
            ->where('user_id', $userId)
            ->whereNull('clock_out_time')
            ->latest('attendance_id')
            ->first();

        if (!$open) {
            return null;
        }

        // Without a schedule we cannot compute the window; keep prior behaviour.
        if (!$open->schedule) {
            return $open;
        }

        $shiftDate = $open->schedule->shift_date;
        switch ($open->schedule->shift_type) {
            case 'morning':
                $cap = \Carbon\Carbon::parse($shiftDate . ' 15:00:00')->addHour(); // 16:00
                break;
            case 'office':
                $cap = \Carbon\Carbon::parse($shiftDate . ' 17:00:00')->addHour(); // 18:00
                break;
            default: // evening / night: ends midnight, grace until 01:00 next day
                $cap = \Carbon\Carbon::parse($shiftDate . ' 23:59:59')->addSecond()->addHour();
                break;
        }

        // Past the grace window => stale forgotten clock-out, not an active session.
        return \Carbon\Carbon::now()->lte($cap) ? $open : null;
    }
}
