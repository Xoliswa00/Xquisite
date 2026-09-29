<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class PublicQuestion extends Model
{
    use Auditable;

    protected $fillable = [
        'public_launch_id',
        'asker_name',
        'asker_email',
        'question',
        'answer',
        'answered_at',
        'is_published',
    ];

    protected $casts = [
        'answered_at'  => 'datetime',
        'is_published' => 'boolean',
    ];

    public function publicLaunch()
    {
        return $this->belongsTo(PublicLaunch::class);
    }

    public function isAnswered(): bool
    {
        return $this->answer !== null;
    }
}
