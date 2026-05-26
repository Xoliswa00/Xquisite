<?php

namespace App\Models\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $table = 'system_logs';

    protected $fillable = [
        'level',
        'message',
        'context',
        'file',
        'line',
        'user_id',
        'request_id',
        'ip_address',
        'url',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
