<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPortal extends Model
{
    protected $table = 'client_portals';

    protected $fillable = [
        'client_id',
        'company_id',
        'token',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
