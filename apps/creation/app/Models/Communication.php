<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Communication extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'client_id',
        'from_user_id',
        'to_user_id',
        'subject',
        'body',
        'is_from_owner',
        'read_at',
    ];

    protected $casts = [
        'read_at'       => 'datetime',
        'is_from_owner' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(company::class);
    }

    public function client()
    {
        return $this->belongsTo(client::class);
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
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }
}
