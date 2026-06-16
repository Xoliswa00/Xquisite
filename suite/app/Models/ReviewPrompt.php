<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewPrompt extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'threshold', 'shown_at', 'dismissed_at', 'review_id'];

    protected $casts = [
        'shown_at'     => 'datetime',
        'dismissed_at' => 'datetime',
    ];

    public function user()   { return $this->belongsTo(User::class); }
    public function review() { return $this->belongsTo(Review::class); }

    public static function nextThresholdFor(int $userId, int $auditCount): ?int
    {
        $prompted = static::where('user_id', $userId)
            ->pluck('threshold')
            ->all();

        foreach (Review::THRESHOLDS as $threshold) {
            if ($auditCount >= $threshold && ! in_array($threshold, $prompted)) {
                return $threshold;
            }
        }

        return null;
    }
}
