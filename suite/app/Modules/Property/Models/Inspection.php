<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasTenant, Auditable;

    protected $fillable = ['tenant_id', 'lease_id', 'type', 'status', 'completed_by', 'completed_at', 'notes'];

    protected $casts = ['completed_at' => 'datetime'];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function sections()
    {
        return $this->hasMany(InspectionSection::class);
    }

    public function isComplete(): bool
    {
        return $this->sections->every(fn (InspectionSection $s) => $s->photo_path !== null);
    }
}
