<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class PublicLaunch extends Model
{
    use Auditable;

    protected $fillable = [
        'key',
        'title',
        'tagline',
        'benefits',
        'launch_at',
        'is_active',
        'qa_enabled',
    ];

    protected $casts = [
        'benefits'   => 'array',
        'launch_at'  => 'datetime',
        'is_active'  => 'boolean',
        'qa_enabled' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(PublicQuestion::class);
    }

    public function publishedQuestions()
    {
        return $this->questions()->where('is_published', true)->latest('answered_at');
    }

    public function hasCountdown(): bool
    {
        return $this->launch_at !== null && $this->launch_at->isFuture();
    }
}
