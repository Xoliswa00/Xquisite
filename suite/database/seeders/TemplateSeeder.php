<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key'         => 'grandure-coming-soon',
                'name'        => 'Grandure — Coming Soon',
                'description' => 'A clean coming-soon page with a countdown timer and email signup — perfect while you get the rest of your site ready.',
                'category'    => 'coming-soon',
                'blade_view'  => 'site-templates.grandure-coming-soon',
                'price_type'  => 'free',
                'is_featured' => true,
                'sort_order'  => 1,
            ],
            [
                'key'         => 'eat-restaurant',
                'name'        => 'Eat & Chat — Restaurant',
                'description' => 'A warm, image-led one-pager for restaurants and cafés — menu highlights, gallery, testimonials, and a contact section.',
                'category'    => 'restaurant',
                'blade_view'  => 'site-templates.eat-restaurant',
                'price_type'  => 'free',
                'sort_order'  => 2,
            ],
            [
                'key'         => 'add-life-fitness',
                'name'        => 'Add Life — Health & Fitness',
                'description' => 'A bold one-pager for gyms and fitness studios — classes, trainers, gallery, pricing, and live stats.',
                'category'    => 'fitness',
                'blade_view'  => 'site-templates.add-life-fitness',
                'price_type'  => 'free',
                'sort_order'  => 3,
            ],
        ];

        foreach ($templates as $data) {
            Template::updateOrCreate(['key' => $data['key']], $data);
        }
    }
}
