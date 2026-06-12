<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class company_invitations extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyInvitationsFactory> */
    use HasFactory;
 protected $fillable = [
        'company_id',
        'email',
        'role',
        'token',
        'expires_at',
        'client_id',
         'status',  

    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
   
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
}
