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
                'default_primary_color'   => '#1B727B',
                'default_secondary_color' => '#416467',
                'default_accent_color'    => '#EDB400',
                'version'     => '1.0.0',
                'author'      => 'Xquisite',
                'supports_theme_toggle' => false,
                'modules_supported' => [],
                'changelog'   => [['version' => '1.0.0', 'date' => '2026-08-04', 'notes' => 'Initial release.']],
            ],
            [
                'key'         => 'eat-restaurant',
                'name'        => 'Eat & Chat — Restaurant',
                'description' => 'A warm, image-led one-pager for restaurants and cafés — menu highlights, gallery, testimonials, and a contact section.',
                'category'    => 'restaurant',
                'blade_view'  => 'site-templates.sections-page',
                'price_type'  => 'free',
                'sort_order'  => 2,
                'default_primary_color'   => '#CC580C',
                'default_secondary_color' => '#333333',
                'default_accent_color'    => '#F39C12',
                'version'     => '1.2.0',
                'author'      => 'Xquisite',
                'supports_theme_toggle' => true,
                'modules_supported' => ['booking', 'pos'],
                'changelog'   => [
                    ['version' => '1.2.0', 'date' => '2026-08-05', 'notes' => 'Rebuilt on the section builder — every part of the page is now editable, reorderable, and reusable.'],
                    ['version' => '1.1.0', 'date' => '2026-08-05', 'notes' => 'Added custom About photo, tenant-uploadable gallery, and Font Awesome icon fixes.'],
                    ['version' => '1.0.0', 'date' => '2026-08-04', 'notes' => 'Initial release.'],
                ],
            ],
            [
                'key'         => 'add-life-fitness',
                'name'        => 'Add Life — Health & Fitness',
                'description' => 'A bold one-pager for gyms and fitness studios — classes, trainers, gallery, pricing, and live stats.',
                'category'    => 'fitness',
                'blade_view'  => 'site-templates.sections-page',
                'price_type'  => 'free',
                'sort_order'  => 3,
                'default_primary_color'   => '#F9690E',
                'default_secondary_color' => '#2C8CB3',
                'default_accent_color'    => '#2CAAB3',
                'version'     => '1.2.0',
                'author'      => 'Xquisite',
                'supports_theme_toggle' => true,
                'modules_supported' => ['booking'],
                'changelog'   => [
                    ['version' => '1.2.0', 'date' => '2026-08-05', 'notes' => 'Rebuilt on the section builder — every part of the page is now editable, reorderable, and reusable.'],
                    ['version' => '1.1.0', 'date' => '2026-08-05', 'notes' => 'Added custom About photo and tenant-uploadable gallery.'],
                    ['version' => '1.0.0', 'date' => '2026-08-04', 'notes' => 'Initial release.'],
                ],
            ],
            [
                'key'         => 'aroma-beauty-spa',
                'name'        => 'Aroma — Beauty & Spa',
                'description' => 'A calming one-pager for salons and spas — treatments, team, gallery, and package pricing.',
                'category'    => 'beauty-spa',
                'blade_view'  => 'site-templates.sections-page',
                'price_type'  => 'free',
                'sort_order'  => 4,
                'default_primary_color'   => '#3B8838',
                'default_secondary_color' => '#2C8CB3',
                'default_accent_color'    => '#71DE6C',
                'version'     => '1.2.0',
                'author'      => 'Xquisite',
                'supports_theme_toggle' => true,
                'modules_supported' => ['booking'],
                'changelog'   => [
                    ['version' => '1.2.0', 'date' => '2026-08-05', 'notes' => 'Rebuilt on the section builder — every part of the page is now editable, reorderable, and reusable.'],
                    ['version' => '1.1.0', 'date' => '2026-08-05', 'notes' => 'Added custom About photo, tenant-uploadable gallery, and Font Awesome icon fixes.'],
                    ['version' => '1.0.0', 'date' => '2026-08-04', 'notes' => 'Initial release.'],
                ],
            ],
            [
                'key'         => 'beauty-salon',
                'name'        => 'Beauty Salon — Hair & Styling',
                'description' => 'A polished one-pager for hair salons and stylists — services, gallery, team, and a review-ready contact section.',
                'category'    => 'beauty-spa',
                'blade_view'  => 'site-templates.sections-page',
                'price_type'  => 'free',
                'sort_order'  => 6,
                'default_primary_color'   => '#C2185B',
                'default_secondary_color' => '#3A2E39',
                'default_accent_color'    => '#D4AF7F',
                'version'     => '1.1.0',
                'author'      => 'Xquisite',
                'supports_theme_toggle' => true,
                'modules_supported' => ['booking'],
                'changelog'   => [
                    ['version' => '1.1.0', 'date' => '2026-08-05', 'notes' => 'Rebuilt on the section builder — every part of the page is now editable, reorderable, and reusable.'],
                    ['version' => '1.0.0', 'date' => '2026-08-05', 'notes' => 'Initial release — a second beauty-spa option alongside Aroma, aimed at hair salons specifically.'],
                ],
            ],
            [
                'key'         => 'lovely-wedding',
                'name'        => 'Lovely — Wedding & Event Planning',
                'description' => 'An elegant one-pager for wedding and event planners — packages, gallery, team, and a booking-ready contact section.',
                'category'    => 'wedding-events',
                'blade_view'  => 'site-templates.sections-page',
                'price_type'  => 'free',
                'sort_order'  => 5,
                'default_primary_color'   => '#ED5441',
                'default_secondary_color' => '#AD0C98',
                'default_accent_color'    => '#49B5E7',
                'version'     => '1.2.0',
                'author'      => 'Xquisite',
                'supports_theme_toggle' => true,
                'modules_supported' => ['booking', 'client_messaging'],
                'changelog'   => [
                    ['version' => '1.2.0', 'date' => '2026-08-05', 'notes' => 'Rebuilt on the section builder — every part of the page is now editable, reorderable, and reusable.'],
                    ['version' => '1.1.0', 'date' => '2026-08-05', 'notes' => 'Added custom About photo and tenant-uploadable gallery.'],
                    ['version' => '1.0.0', 'date' => '2026-08-04', 'notes' => 'Initial release.'],
                ],
            ],
        ];

        foreach ($templates as $data) {
            Template::updateOrCreate(['key' => $data['key']], $data);
        }
    }
}
