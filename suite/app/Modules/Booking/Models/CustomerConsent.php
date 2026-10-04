<?php

namespace App\Modules\Booking\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * One line in a client's consent history: what they were told or agreed to,
 * when, and how. Append-only (POPIA evidence), written only through
 * App\Services\Booking\ConsentLedger; current state lives on the record it
 * concerns (e.g. appointments.look_showcase_at) for cheap reads.
 */
class CustomerConsent extends Model
{
    use HasTenant, Auditable;

    /** Staff saved a look of the client (notified) / the client removed it (withdrawn). */
    public const SCOPE_LOOK_SAVED = 'look_saved';

    /** The client allows the business to share a saved look (portfolio, social media). */
    public const SCOPE_LOOK_SHOWCASE = 'look_showcase';

    /** "Time to rebook" messages (the client can withdraw). */
    public const SCOPE_REBOOK_REMINDERS = 'rebook_reminders';

    public const NOTIFIED  = 'notified';
    public const GRANTED   = 'granted';
    public const WITHDRAWN = 'withdrawn';

    protected $fillable = ['tenant_id', 'customer_id', 'appointment_id', 'scope', 'action', 'via', 'ip'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /** Plain-language line for the customer record's consent history. */
    public function describe(): string
    {
        $what = match ($this->scope) {
            self::SCOPE_LOOK_SAVED       => 'Saved look',
            self::SCOPE_LOOK_SHOWCASE    => 'Sharing a look',
            self::SCOPE_REBOOK_REMINDERS => 'Rebook reminders',
            default                      => ucfirst(str_replace('_', ' ', $this->scope)),
        };

        $how = match ($this->action) {
            self::NOTIFIED  => 'client was told',
            self::GRANTED   => 'client agreed',
            self::WITHDRAWN => $this->via === 'client' ? 'client withdrew' : 'withdrawn',
            default         => $this->action,
        };

        return "{$what}: {$how}";
    }
}
