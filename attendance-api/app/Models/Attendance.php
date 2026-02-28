<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'type',
        'status',
        'lat',
        'lng',
        'face_confidence',
        'liveness_score',
        'note',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
            'face_confidence' => 'decimal:4',
            'liveness_score' => 'decimal:4',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class , 'session_id');
    }

    public function scopeCheckin($query)
    {
        return $query->where('type', 'checkin');
    }

    public function scopeCheckout($query)
    {
        return $query->where('type', 'checkout');
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereHas('session', function ($q) use ($date) {
            $q->where('date', $date);
        });
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->whereHas('user', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });
    }
}
