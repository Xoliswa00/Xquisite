<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\CompanyScope;


abstract class TenantModel extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }
}