<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class ApplicantDocument extends Model
{
    use HasTenant, Auditable;

    protected $fillable = ['tenant_id', 'applicant_id', 'type', 'path', 'original_name'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }
}
