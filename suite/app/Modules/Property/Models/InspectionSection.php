<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class InspectionSection extends Model
{
    use HasTenant, Auditable;

    protected $fillable = ['tenant_id', 'inspection_id', 'name', 'condition', 'notes', 'photo_path'];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function url(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }
}
