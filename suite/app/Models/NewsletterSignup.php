<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSignup extends Model
{
    protected $fillable = [
        'tenant_id',
        'section_id',
        'email',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function section()
    {
        return $this->belongsTo(TenantPageSection::class, 'section_id');
    }
}
