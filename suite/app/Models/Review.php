<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasTenant;
    protected $fillable = [
        'user_id',
        'tenant_id',
        'rating',
        'title',
        'body',
        'status',
        'is_featured',
        'business_type',
        'display_name',
        'prompted_at_count',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'is_featured' => 'boolean',
    ];

    const THRESHOLDS = [100, 500, 1000];

    public function user()   { return $this->belongsTo(User::class); }
    public function tenant() { return $this->belongsTo(Tenant::class); }

    public function scopeApproved(Builder $q): Builder
    {
        return $q->where('status', 'approved');
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }

    public function scopePublic(Builder $q): Builder
    {
        return $q->approved()->latest();
    }

    public function starsHtml(): string
    {
        $filled = str_repeat('★', $this->rating);
        $empty  = str_repeat('☆', 5 - $this->rating);
        return $filled . $empty;
    }
}
