<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDomain extends Model
{
    use HasFactory;

    protected $table = 'company_domains';

    protected $fillable = [
        'company_id',
        'domain',
        'domain_verified_at',
    ];

    protected $casts = [
        'domain_verified_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
