<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Modules\Booking\Models\ServicePhoto;
use Illuminate\Database\Eloquent\Model;

class PhotoReport extends Model
{
    use Auditable;

    protected $fillable = [
        'service_photo_id', 'tenant_id', 'reason', 'reporter_ip', 'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function servicePhoto()
    {
        return $this->belongsTo(ServicePhoto::class);
    }
}
