<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = 'shift_schedules';
    protected $primaryKey = 'shift_id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'shift_type',
        'shift_date',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class, 'shift_id');
    }
} 