<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantBranding extends Model
{
    protected $table = 'tenant_branding';

    protected $fillable = [
        'tenant_id',
        'favicon_url',
        'hero_image_url',
        'primary_color',
        'secondary_color',
        'accent_color',
        'heading_font',
        'body_font',
        'description',
        'contact_email',
        'contact_phone',
        'whatsapp_number',
        'socials',
        'business_hours',
    ];

    protected $casts = [
        'socials'        => 'array',
        'business_hours' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getDisplayEmailAttribute(): ?string
    {
        return $this->contact_email ?: $this->tenant?->email;
    }

    public function getDisplayPhoneAttribute(): ?string
    {
        return $this->contact_phone ?: $this->tenant?->phone;
    }
}
