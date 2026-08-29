<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class MaintenancePhoto extends Model
{
    use HasTenant, Auditable;

    protected $fillable = ['tenant_id', 'maintenance_request_id', 'path', 'caption'];

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }
}
