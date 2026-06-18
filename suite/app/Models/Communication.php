<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Communication extends Model
{
    use HasTenant;
    protected $fillable = [
        'tenant_id', 'client_id', 'from_user_id', 'to_user_id',
        'subject', 'body', 'is_from_owner', 'read_at',
    ];

    protected $casts = [
        'is_from_owner' => 'boolean',
        'read_at'       => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function markRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }
}
