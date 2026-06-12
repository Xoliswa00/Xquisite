<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'color',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(company::class);
    }

    public function services()
    {
        return $this->hasMany(product::class, 'service_category_id');
    }

    public function activeServices()
    {
        return $this->hasMany(product::class, 'service_category_id')
                    ->where('is_active', true);
    }

    // Tailwind-safe color map for the booking page
    public static function colorClasses(): array
    {
        return [
            'slate'  => ['bg' => 'bg-slate-100',  'text' => 'text-slate-700',  'border' => 'border-slate-300',  'accent' => 'bg-slate-700'],
            'rose'   => ['bg' => 'bg-rose-50',    'text' => 'text-rose-700',   'border' => 'border-rose-200',   'accent' => 'bg-rose-600'],
            'pink'   => ['bg' => 'bg-pink-50',    'text' => 'text-pink-700',   'border' => 'border-pink-200',   'accent' => 'bg-pink-600'],
            'violet' => ['bg' => 'bg-violet-50',  'text' => 'text-violet-700', 'border' => 'border-violet-200', 'accent' => 'bg-violet-600'],
            'amber'  => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',  'border' => 'border-amber-200',  'accent' => 'bg-amber-500'],
            'emerald'=> ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700','border' => 'border-emerald-200','accent' => 'bg-emerald-600'],
            'sky'    => ['bg' => 'bg-sky-50',     'text' => 'text-sky-700',    'border' => 'border-sky-200',    'accent' => 'bg-sky-600'],
            'orange' => ['bg' => 'bg-orange-50',  'text' => 'text-orange-700', 'border' => 'border-orange-200', 'accent' => 'bg-orange-500'],
        ];
    }

    public function colorClass(string $type = 'bg'): string
    {
        return static::colorClasses()[$this->color][$type]
            ?? static::colorClasses()['slate'][$type];
    }
}
