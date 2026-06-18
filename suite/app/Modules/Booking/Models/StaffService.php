<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;

class StaffService extends Model
{
    protected $table = 'staff_services';

    protected $fillable = [
        'staff_id',
        'service_id',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
