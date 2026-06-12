<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class company_user extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyUserFactory> */
    use HasFactory;
    protected $table = 'company_users';
    protected $fillable = [
        'company_id',
        'user_id',
        'role',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
