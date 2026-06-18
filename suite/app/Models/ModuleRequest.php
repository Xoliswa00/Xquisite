<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleRequest extends Model
{
    use HasTenant;
    protected $fillable = [
        'tenant_id',
        'user_id',
        'module',
        'type',
        'status',
        'notes',
        'review_notes',
        'price_override',
        'reviewed_by',
        'requested_at',
        'reviewed_at',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
        'requested_at'  => 'datetime',
        'reviewed_at'   => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getModuleNameAttribute(): string
    {
        return config("modules.{$this->module}.name") ?? ucfirst(str_replace('_', ' ', $this->module));
    }

    public function getReadableTypeAttribute(): string
    {
        return $this->type === 'modification' ? 'Modification' : 'Activation';
    }
}
