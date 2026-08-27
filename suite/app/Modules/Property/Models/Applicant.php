<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasTenant, Auditable;

    protected $fillable = [
        'tenant_id', 'property_id', 'unit_id', 'name', 'email', 'phone',
        'id_number', 'employer', 'employment_type', 'employment_months',
        'monthly_income', 'monthly_expenses', 'number_of_occupants',
        'previous_landlord_name', 'previous_landlord_phone', 'notes',
        'status', 'screened_at', 'screened_by', 'rejection_reason',
    ];

    protected $casts = [
        'monthly_income'    => 'decimal:2',
        'monthly_expenses'  => 'decimal:2',
        'screened_at'       => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function renter()
    {
        return $this->hasOne(Renter::class);
    }

    public function documents()
    {
        return $this->hasMany(ApplicantDocument::class);
    }

    /** Rent this applicant is being assessed against — the specific unit if picked, else null (can't assess without one). */
    public function assessedRent(): ?float
    {
        return $this->unit ? (float) $this->unit->monthly_rent : null;
    }

    /** Rent as a percentage of gross monthly income — the standard affordability check. Null if there's not enough data to assess. */
    public function rentToIncomeRatio(): ?float
    {
        $rent = $this->assessedRent();
        if (!$rent || !$this->monthly_income || (float) $this->monthly_income <= 0) {
            return null;
        }

        return round(($rent / (float) $this->monthly_income) * 100, 1);
    }

    /** Income left after rent and declared monthly expenses. Null if there's not enough data. */
    public function disposableIncome(): ?float
    {
        $rent = $this->assessedRent();
        if (!$rent || !$this->monthly_income) {
            return null;
        }

        return (float) $this->monthly_income - $rent - (float) ($this->monthly_expenses ?? 0);
    }

    /**
     * good: rent ≤30% of income (standard affordability guideline), caution: ≤40%, risk: over.
     * Returns null when there isn't enough data to judge.
     */
    public function affordabilityRating(): ?string
    {
        $ratio = $this->rentToIncomeRatio();
        if ($ratio === null) {
            return null;
        }

        return match (true) {
            $ratio <= 30 => 'good',
            $ratio <= 40 => 'caution',
            default      => 'risk',
        };
    }
}
