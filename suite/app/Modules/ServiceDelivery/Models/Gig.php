<?php

namespace App\Modules\ServiceDelivery\Models;

use App\Models\Client;
use App\Models\Quote;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Gig extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'client_id', 'category', 'title', 'description',
        'discovery_notes', 'status', 'hourly_rate', 'deadline_date',
        'started_at', 'completed_at', 'invoice_status', 'notes',
    ];

    protected $casts = [
        'deadline_date' => 'date',
        'started_at'    => 'date',
        'completed_at'  => 'date',
        'hourly_rate'   => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(GigTimeEntry::class);
    }

    /** The most recently created quote for this gig — treated as the "current" one. */
    public function currentQuote(): ?Quote
    {
        return $this->quotes->sortByDesc('id')->first();
    }

    public function totalMinutesLogged(): int
    {
        return (int) $this->timeEntries()->sum('minutes');
    }
}
