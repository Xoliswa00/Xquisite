<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Services\Tenant\TenantContext;

trait HasTenant
{
    protected function initializeHasTenant(): void
    {
        $this->mergeCasts(['tenant_id' => 'integer']);
    }

    protected static function bootHasTenant(): void
    {
        static::creating(function ($model) {
            // Fill only if not already set. Some flows (e.g. the public
            // storefront/checkout, which resolves its tenant from a
            // {tenantSlug} route parameter and never touches TenantContext)
            // legitimately create records for a tenant other than whatever
            // TenantContext holds for the current session — an ambient
            // "context always wins" override would silently mis-file those
            // records under the wrong tenant. Callers that mass-assign
            // request input are responsible for not exposing tenant_id as
            // an assignable field.
            if (!$model->tenant_id) {
                $model->tenant_id = TenantContext::get();
            }
        });

        // tenant_id is immutable once a row exists — there is no legitimate
        // "move this record to another tenant" feature in this app.
        static::updating(function ($model) {
            if ($model->isDirty('tenant_id')) {
                throw new \RuntimeException(
                    'tenant_id cannot be changed after creation on ' . get_class($model) . '.'
                );
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = TenantContext::get();

            if ($tenantId) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });
    }
}
