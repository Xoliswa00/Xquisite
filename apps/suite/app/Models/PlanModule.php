<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanModule extends Model
{
    public $timestamps = false;

    protected $fillable = ['plan_id', 'module_key'];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function platformModule()
    {
        return $this->belongsTo(PlatformModule::class, 'module_key', 'key');
    }
}
