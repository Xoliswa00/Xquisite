<?php

namespace App\Models\Traits;

trait HasTenant
{
protected static function bootHasTenant()
    {
        static::creating(function ($model) {
            if (!$model->tenant_id) {
                $model->tenant_id = TenantContext::get();
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = TenantContext::get();

            if ($tenantId) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });
    }}
