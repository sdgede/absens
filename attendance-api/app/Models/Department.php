<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch()
    {
        return $this->belongsTo(TenantBranch::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
