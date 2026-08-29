<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class LeaseDepositDeduction extends Model
{
    use HasTenant, Auditable;

    protected $fillable = ['tenant_id', 'lease_id', 'description', 'amount'];

    protected $casts = ['amount' => 'decimal:2'];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }
}
