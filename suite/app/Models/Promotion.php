<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasTenant;
    protected $fillable = [
        'tenant_id', 'name', 'description', 'code', 'discount_type',
        'discount_value', 'applies_to', 'valid_from', 'valid_until',
        'max_uses', 'used_count', 'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'valid_from'     => 'datetime',
        'valid_until'    => 'datetime',
        'max_uses'       => 'integer',
        'used_count'     => 'integer',
        'is_active'      => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isLive(): bool
    {
        if (!$this->is_active) return false;
        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) return false;
        if ($this->valid_until && $now->gt($this->valid_until)) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;
        return true;
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) return 'Inactive';
        if ($this->max_uses && $this->used_count >= $this->max_uses) return 'Exhausted';
        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) return 'Scheduled';
        if ($this->valid_until && $now->gt($this->valid_until)) return 'Expired';
        return 'Live';
    }
}
