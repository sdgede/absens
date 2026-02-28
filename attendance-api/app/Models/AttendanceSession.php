<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'date',
        'check_in_start',
        'check_in_end',
        'late_after',
        'check_out_start',
        'check_out_end',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch()
    {
        return $this->belongsTo(TenantBranch::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class , 'session_id');
    }
}
