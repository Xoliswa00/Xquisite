<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    use HasTenant, Auditable;

    protected $fillable = ['tenant_id', 'property_id', 'path'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }
}
