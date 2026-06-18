<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Modules\POS\Models\Product;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasTenant;
    protected $fillable = [
        'tenant_id', 'name', 'description', 'color', 'icon', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];

    public static function colorClasses(): array
    {
        return [
            'indigo'  => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'dot' => 'bg-indigo-500'],
            'violet'  => ['bg' => 'bg-violet-100', 'text' => 'text-violet-700', 'border' => 'border-violet-200', 'dot' => 'bg-violet-500'],
            'pink'    => ['bg' => 'bg-pink-100',   'text' => 'text-pink-700',   'border' => 'border-pink-200',   'dot' => 'bg-pink-500'],
            'rose'    => ['bg' => 'bg-rose-100',   'text' => 'text-rose-700',   'border' => 'border-rose-200',   'dot' => 'bg-rose-500'],
            'amber'   => ['bg' => 'bg-amber-100',  'text' => 'text-amber-700',  'border' => 'border-amber-200',  'dot' => 'bg-amber-500'],
            'emerald' => ['bg' => 'bg-emerald-100','text' => 'text-emerald-700','border' => 'border-emerald-200','dot' => 'bg-emerald-500'],
            'sky'     => ['bg' => 'bg-sky-100',    'text' => 'text-sky-700',    'border' => 'border-sky-200',    'dot' => 'bg-sky-500'],
            'slate'   => ['bg' => 'bg-slate-100',  'text' => 'text-slate-700',  'border' => 'border-slate-200',  'dot' => 'bg-slate-500'],
        ];
    }

    public function colorClass(string $type): string
    {
        return self::colorClasses()[$this->color][$type] ?? '';
    }

    public static function guessIcon(string $name): string
    {
        $name = mb_strtolower($name);

        $map = [
            // Hair
            'hair'      => '💇', 'cut'       => '✂️', 'trim'    => '✂️', 'colour'  => '🎨',
            'color'     => '🎨', 'highlight' => '🌟', 'blowout' => '💨', 'blowdry' => '💨',
            'braid'     => '🪢', 'loc'       => '🪢', 'weave'   => '🪢', 'wig'     => '👸',
            'relaxer'   => '🧴', 'perm'      => '🌀', 'keratin' => '✨',
            // Nails
            'nail'      => '💅', 'mani'      => '💅', 'pedi'    => '🦶', 'gel'     => '💎',
            'acrylic'   => '💎', 'lash'      => '👁️', 'brow'    => '🤨',
            // Skin / Face
            'facial'    => '🧖', 'skin'      => '🧖', 'peel'    => '✨', 'cleanse' => '🫧',
            'wax'       => '🪒', 'thread'    => '🧵', 'shave'   => '🪒', 'beard'   => '🧔',
            // Body
            'massage'   => '💆', 'body'      => '🛁', 'wrap'    => '🛁', 'scrub'   => '🧼',
            'steam'     => '♨️',  'sauna'     => '🔥', 'detox'   => '🌿',
            // Makeup
            'makeup'    => '💄', 'make-up'   => '💄', 'foundation' => '💄', 'lipstick' => '💄',
            'contour'   => '💄', 'airbrush'  => '🎨', 'bridal'  => '👰',
            // Wellness
            'yoga'      => '🧘', 'spa'       => '🛁', 'wellness' => '🌿', 'health'  => '💚',
            'consult'   => '📋', 'package'   => '📦', 'combo'   => '⭐',
        ];

        foreach ($map as $keyword => $emoji) {
            if (str_contains($name, $keyword)) {
                return $emoji;
            }
        }

        return '✨';
    }

    public function services()
    {
        return $this->hasMany(Product::class, 'service_category_id');
    }

    public function activeServices()
    {
        return $this->hasMany(Product::class, 'service_category_id')->where('is_active', true);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
