<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'code',
        'discount_type',
        'discount_value',
        'applies_to',
        'valid_from',
        'valid_until',
        'max_uses',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'valid_from'     => 'datetime',
        'valid_until'    => 'datetime',
        'is_active'      => 'boolean',
        'discount_value' => 'decimal:2',
        'max_uses'       => 'integer',
        'used_count'     => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(company::class);
    }

    public function isLive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) return 'Inactive';
        if ($this->valid_from && now()->lt($this->valid_from)) return 'Scheduled';
        if ($this->valid_until && now()->gt($this->valid_until)) return 'Expired';
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) return 'Exhausted';
        return 'Live';
    }
}
