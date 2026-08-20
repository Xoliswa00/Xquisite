<?php

namespace App\Modules\ServiceDelivery\Models;

use App\Models\Traits\HasTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GigTimeEntry extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'gig_id', 'description', 'minutes', 'logged_by', 'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'date',
    ];

    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }

    public function loggedBy()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
