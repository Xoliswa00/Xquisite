<?php

namespace App\Modules\Booking\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\HasTenant;
use App\Notifications\PortalResetPasswordNotification;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;
use NotificationChannels\WebPush\HasPushSubscriptions;

class Customer extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use HasTenant, Auditable, Authenticatable, Notifiable, SoftDeletes, CanResetPassword, HasPushSubscriptions;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'notes',
        'is_active',
        'password',
        'rebook_reminders_opt_out_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active'         => 'boolean',
        'email_verified_at' => 'datetime',
        'rebook_reminders_opt_out_at' => 'datetime',
        'password'          => 'hashed',
        'setup_link_version'    => 'integer',
        'setup_link_expires_at' => 'datetime',
    ];

    /** How long a login setup link works for. Short, because sending a new one is one tap. */
    public const SETUP_LINK_HOURS = 48;

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /** What this client was told or agreed to, newest first (see ConsentLedger). */
    public function consents()
    {
        return $this->hasMany(CustomerConsent::class)->latest('id');
    }

    public function wantsRebookReminders(): bool
    {
        return $this->is_active && $this->rebook_reminders_opt_out_at === null;
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    // ── Login setup link ────────────────────────────────────────────────────
    // Staff create it on purpose and send it to the customer, who chooses their
    // own password. The version is part of the signed address, so a new link, a
    // cancel, or using the link all make every earlier link worthless.

    public function hasActiveSetupLink(): bool
    {
        return $this->setup_link_expires_at?->isFuture() ?? false;
    }

    public function issueSetupLink(): string
    {
        $this->forceFill([
            'setup_link_version'    => $this->setup_link_version + 1,
            'setup_link_expires_at' => now()->addHours(self::SETUP_LINK_HOURS)->startOfSecond(),
        ])->save();

        return $this->setupLinkUrl();
    }

    public function cancelSetupLink(): void
    {
        $this->forceFill([
            'setup_link_version'    => $this->setup_link_version + 1,
            'setup_link_expires_at' => null,
        ])->save();
    }

    public function acceptsSetupLink(mixed $version): bool
    {
        return $this->hasActiveSetupLink() && (string) $version === (string) $this->setup_link_version;
    }

    /** The same address every time while the link is active, so it can be shown again without making a new one. */
    public function setupLinkUrl(): ?string
    {
        if (! $this->hasActiveSetupLink() || ! $this->tenant?->slug) {
            return null;
        }

        return URL::temporarySignedRoute('book.claim.setup', $this->setup_link_expires_at, [
            'slug'     => $this->tenant->slug,
            'customer' => $this->id,
            'v'        => $this->setup_link_version,
        ]);
    }

    /** Link on its own line: a long address in the middle of a sentence looks like a scam. */
    public function setupLinkMessage(): ?string
    {
        if (! ($url = $this->setupLinkUrl())) {
            return null;
        }

        return "Hi {$this->name}, this is {$this->tenant->name}. Here is your personal link to choose a password for online booking:"
            . "\n{$url}\nIt works until " . $this->setup_link_expires_at->format('l H:i') . '. We will never ask you for your password.';
    }

    /** 0821234567 for any way a South African cell number is usually written, or null. */
    public static function normalisePhone(?string $raw): ?string
    {
        if (! $raw) {
            return null;
        }

        $digits = preg_replace('/[^\d+]/', '', $raw);

        if (str_starts_with($digits, '+27')) {
            $digits = '0' . substr($digits, 3);
        } elseif (str_starts_with($digits, '27') && strlen($digits) === 11) {
            $digits = '0' . substr($digits, 2);
        }

        return preg_match('/^0\d{9}$/', $digits) ? $digits : null;
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = route('book.password.reset', ['slug' => $this->tenant->slug, 'token' => $token])
            . '?email=' . urlencode($this->email);

        $this->notify(new PortalResetPasswordNotification($url, 'Customer Portal', $this->tenant->name));
    }
}
