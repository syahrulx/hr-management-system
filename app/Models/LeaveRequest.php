<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $table = 'leave_requests';
    protected $guarded = [];
    protected $primaryKey = 'request_id';
    public $timestamps = false;

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => match ($value) {
                0 => 'Pending',
                1 => 'Approved',
                2 => 'Rejected',
            },
        );
    }
}

