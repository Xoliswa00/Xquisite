<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenantServiceOrder extends Model
{
    protected $fillable = [
        'platform_service_id', 'tenant_id', 'requested_by', 'assigned_to',
        'status', 'quoted_price', 'client_notes', 'admin_notes',
        'requested_date', 'delivered_at',
    ];

    protected $casts = [
        'quoted_price'   => 'decimal:2',
        'requested_date' => 'date',
        'delivered_at'   => 'date',
    ];

    const STATUS_LABELS = [
        'requested'   => 'Requested',
        'quoted'      => 'Quoted',
        'approved'    => 'Approved',
        'in_progress' => 'In Progress',
        'delivered'   => 'Delivered',
        'cancelled'   => 'Cancelled',
    ];

    const STATUS_COLOURS = [
        'requested'   => 'amber',
        'quoted'      => 'blue',
        'approved'    => 'indigo',
        'in_progress' => 'purple',
        'delivered'   => 'emerald',
        'cancelled'   => 'gray',
    ];

    public function service()    { return $this->belongsTo(PlatformService::class, 'platform_service_id'); }
    public function tenant()     { return $this->belongsTo(Tenant::class); }
    public function requester()  { return $this->belongsTo(User::class, 'requested_by'); }
    public function assignee()   { return $this->belongsTo(User::class, 'assigned_to'); }

    public function scopePending(Builder $q): Builder
    {
        return $q->whereIn('status', ['requested', 'quoted', 'approved', 'in_progress']);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function statusColour(): string
    {
        return self::STATUS_COLOURS[$this->status] ?? 'gray';
    }
}
