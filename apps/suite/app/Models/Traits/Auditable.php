<?php

namespace App\Models\Traits;

trait Auditable
{
    //
      public static function bootAuditable()
    {
        static::created(function ($model) {
            AuditService::log(
                action: class_basename($model) . '.created',
                entityType: get_class($model),
                entityId: $model->id,
                new: $model->toArray()
            );
        });

        static::updated(function ($model) {
            AuditService::log(
                action: class_basename($model) . '.updated',
                entityType: get_class($model),
                entityId: $model->id,
                old: $model->getOriginal(),
                new: $model->getChanges()
            );
        });

        static::deleted(function ($model) {
            AuditService::log(
                action: class_basename($model) . '.deleted',
                entityType: get_class($model),
                entityId: $model->id,
                old: $model->toArray()
            );
        });
    }
}
