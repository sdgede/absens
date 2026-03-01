<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\TenantScope;

class TenantBranch extends Model
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }
    protected $fillable = [
        'tenant_id',
        'name',
        'address',
        'lat',
        'lng',
        'radius_meter',
        'timezone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class , 'branch_id');
    }

    public function users()
    {
        return $this->hasMany(User::class , 'branch_id');
    }
}
