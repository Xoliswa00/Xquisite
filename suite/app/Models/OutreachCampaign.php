<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutreachCampaign extends Model
{
    use Auditable;

    protected $fillable = [
        'name', 'channel', 'target_business_type', 'planned_count',
        'status', 'started_at', 'notes', 'created_by',
    ];

    protected $casts = [
        'started_at' => 'date',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(FoundingTwentyApplication::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Per-industry breakdown: applied / selected / converted counts, keyed by
     * business_type — the "progress by industry" view.
     */
    public function industryBreakdown(): \Illuminate\Support\Collection
    {
        return $this->applications
            ->groupBy('business_type')
            ->map(fn ($apps) => [
                'applied' => $apps->count(),
                'selected' => $apps->whereIn('status', ['selected', 'converted'])->count(),
                'converted' => $apps->where('status', 'converted')->count(),
                'high_tier' => $apps->where('tier', 'high')->count(),
            ]);
    }
}
