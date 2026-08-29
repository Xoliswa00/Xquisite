<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifiedIp extends Model
{
    use Auditable;

    protected $fillable = ['ip_address', 'note', 'verified_by', 'verified_at'];

    protected $casts = ['verified_at' => 'datetime'];

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public static function isVerified(string $ip): bool
    {
        return static::where('ip_address', $ip)->exists();
    }
}
