<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSummary extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'total_present',
        'total_late',
        'total_absent',
        'total_leave',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
