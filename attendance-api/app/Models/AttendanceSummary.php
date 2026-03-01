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

    protected function casts(): array
    {
        return [
            'month'         => 'integer',
            'year'          => 'integer',
            'total_present' => 'integer',
            'total_late'    => 'integer',
            'total_absent'  => 'integer',
            'total_leave'   => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
