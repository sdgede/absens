<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;


class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Scope hanya aktif jika:
     * 1. 'current_tenant_id' sudah di-bind di app container (artinya middleware sudah jalan)
     * 2. Nilai tenant_id bukan null (super-admin sengaja di-set null untuk bypass)
     */

    public function apply(Builder $builder, Model $model): void
    {
        if (!app()->has('current_tenant_id')) {
            return;
        }

        $tenantId = app('current_tenant_id');

        if ($tenantId === null) {
            return;
        }

        if (!Schema::hasColumn($model->getTable(), 'tenant_id')) {
            return;
        }

        $builder->where(
            $model->getTable() . '.tenant_id',
            $tenantId
        );
    }
}
