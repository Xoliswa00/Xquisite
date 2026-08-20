<?php

namespace App\Modules\ServiceDelivery\Models;

use App\Models\Traits\HasTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ServiceAgreementChange extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'service_agreement_id', 'period', 'description',
        'minutes_used', 'logged_by',
    ];

    public function serviceAgreement()
    {
        return $this->belongsTo(ServiceAgreement::class);
    }

    public function loggedBy()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
