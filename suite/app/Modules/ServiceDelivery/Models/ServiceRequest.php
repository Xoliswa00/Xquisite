<?php

namespace App\Modules\ServiceDelivery\Models;

use App\Models\Client;
use App\Models\Traits\HasTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'name', 'email', 'phone', 'company', 'category',
        'description', 'budget_range', 'timeline', 'ip_address',
        'status', 'reviewed_at', 'reviewed_by', 'converted_client_id', 'converted_gig_id',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function convertedClient()
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }

    public function convertedGig()
    {
        return $this->belongsTo(Gig::class, 'converted_gig_id');
    }

    /**
     * Convert this request into a Client (matched by email, or created) and,
     * for project-based categories, a Gig ready to scope. "Ongoing support"
     * requests only create the Client — a service agreement needs a plan
     * choice a human should make, so that step stays manual.
     */
    public function convert(int $reviewerId): Client
    {
        $client = Client::firstOrCreate(
            ['tenant_id' => $this->tenant_id, 'email' => $this->email],
            ['name' => $this->name, 'phone' => $this->phone]
        );

        $gig = null;

        if ($this->category !== 'ongoing_support') {
            $gig = Gig::create([
                'tenant_id'       => $this->tenant_id,
                'client_id'       => $client->id,
                'category'        => $this->category,
                'title'           => "Request from {$this->name}" . ($this->company ? " ({$this->company})" : ''),
                'discovery_notes' => $this->description
                    . ($this->budget_range ? "\n\nBudget: {$this->budget_range}" : '')
                    . ($this->timeline ? "\nTimeline: {$this->timeline}" : ''),
                'status' => 'lead',
            ]);
        }

        $this->update([
            'status'              => 'converted',
            'reviewed_at'         => now(),
            'reviewed_by'         => $reviewerId,
            'converted_client_id' => $client->id,
            'converted_gig_id'    => $gig?->id,
        ]);

        return $client;
    }

    public function dismiss(int $reviewerId): void
    {
        $this->update([
            'status'      => 'dismissed',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
        ]);
    }
}
