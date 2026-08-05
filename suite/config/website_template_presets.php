<?php

// Starting section layouts for each convertible Template (keyed by Template.key).
// Every item of copy here is transcribed verbatim from the template's former
// monolithic Blade file — nothing invented. `grandure-coming-soon` is
// intentionally absent; it never gets a preset (Template::isPlaceholder()).
//
// Image fields are plain paths relative to public/ (not asset() calls —
// config files are eagerly loaded at framework boot, before the URL
// generator exists, so calling asset() here crashes every console command).
// TemplatePresetRegistry::presetFor() resolves them to full URLs on read.

return [

    'eat-restaurant' => [
        'sections' => [
            [
                'type' => 'hero', 'variant' => 'carousel',
                'content' => [
                    'headline' => 'Good food, good company',
                    'subheadline' => 'Fresh dishes made daily, served with a smile',
                    'cta_text' => 'Eat & Chat', 'cta_link' => '#service',
                    'background_image_url' => 'site-templates/eat-restaurant/img/slide1.jpg',
                    'slides' => [
                        ['image_url' => 'site-templates/eat-restaurant/img/slide1.jpg', 'headline' => 'Good food, good company', 'subheadline' => 'Fresh dishes made daily, served with a smile'],
                        ['image_url' => 'site-templates/eat-restaurant/img/slide2.jpg', 'headline' => 'Made from scratch', 'subheadline' => 'Real ingredients, honest cooking, no shortcuts'],
                        ['image_url' => 'site-templates/eat-restaurant/img/slide3.jpg', 'headline' => 'A table always waiting', 'subheadline' => "Book ahead or walk in — we'll find you a seat"],
                    ],
                ],
            ],
            [
                'type' => 'services', 'variant' => 'cards',
                'content' => [
                    'heading' => 'Eat & Chat',
                    'subtext' => 'Fresh ingredients, honest cooking, and a warm room to enjoy it in — every single day.',
                    'items' => [
                        ['icon' => 'fa-heart', 'title' => 'Local Favourites', 'text' => 'The dishes our regulars order again and again — comfort food done right, every time.'],
                        ['icon' => 'fa-users', 'title' => 'Group Dining', 'text' => 'Big table, big appetite? We cater for groups and gatherings with the same care as a table for two.'],
                        ['icon' => 'fa-fire', 'title' => 'Daily Specials', 'text' => "A new dish every day, built around whatever's freshest that morning."],
                        ['icon' => 'fa-film', 'title' => 'Desserts', 'text' => "House-made sweets to finish the meal — ask your server what's fresh out of the kitchen."],
                        ['icon' => 'fa-cubes', 'title' => 'Custom Cakes', 'text' => 'Order ahead for birthdays, celebrations, or just because — made to order.'],
                        ['icon' => 'fa-envelope', 'title' => 'Private Bookings', 'text' => "Planning something bigger? Get in touch and we'll help you plan the evening."],
                    ],
                ],
            ],
            [
                'type' => 'about', 'variant' => 'image-left',
                'content' => [
                    'heading' => 'Who We Are',
                    'body' => null,
                    'image_url' => 'site-templates/eat-restaurant/img/gallery/img1.jpg',
                    'bullets' => [],
                ],
            ],
            [
                'type' => 'gallery', 'variant' => 'grid',
                'content' => [
                    'heading' => 'Our Gallery',
                    'subtext' => 'A few plates, a few nights, a few reasons to come back.',
                    'images' => collect(range(1, 8))->map(fn ($n) => ['url' => "site-templates/eat-restaurant/img/gallery/img{$n}.jpg", 'tags' => []])->all(),
                    'filters' => [],
                ],
            ],
            [
                'type' => 'testimonials', 'variant' => 'carousel',
                'content' => [
                    'heading' => 'What Our Clients Say', 'subtext' => '',
                    'items' => [
                        ['quote' => "Best meal I've had in months. The staff remembered my order from last time — that's how you know a place is good.", 'name' => 'Priya N.', 'role' => '', 'avatar_url' => null, 'initials' => 'PN'],
                        ['quote' => 'We booked a table for eight with no notice and they still made it feel special. Will be back.', 'name' => 'Marcus T.', 'role' => '', 'avatar_url' => null, 'initials' => 'MT'],
                        ['quote' => 'Everything tastes homemade. Honestly one of the few places where the specials board is worth trusting.', 'name' => 'Aaliyah K.', 'role' => '', 'avatar_url' => null, 'initials' => 'AK'],
                    ],
                ],
            ],
            [
                'type' => 'contact', 'variant' => 'split',
                'content' => ['heading' => 'Contact Us', 'subtext' => ''],
            ],
        ],
    ],

    'add-life-fitness' => [
        'sections' => [
            [
                'type' => 'hero', 'variant' => 'static',
                'content' => [
                    'headline' => 'Stronger than EVER',
                    'subheadline' => 'Join us and start your fitness journey today.',
                    'cta_text' => 'Start Now', 'cta_link' => '#pricing',
                    'background_image_url' => 'site-templates/add-life-fitness/images/banner/banner.jpg',
                    'slides' => [],
                ],
            ],
            [
                'type' => 'services', 'variant' => 'cards',
                'content' => [
                    'heading' => "What's Best For You", 'subtext' => '',
                    'items' => [
                        ['icon' => 'fa-futbol-o', 'title' => 'Aerobic', 'text' => 'High-energy group classes built to get your heart rate up and keep you coming back.'],
                        ['icon' => 'fa-compass', 'title' => 'Cardio', 'text' => 'Structured cardio programs for every fitness level, from first-timers to regulars.'],
                        ['icon' => 'fa-database', 'title' => 'Strength Training', 'text' => 'Build real strength with guided weight training and progressive programs.'],
                        ['icon' => 'fa-bar-chart', 'title' => 'Group Classes', 'text' => 'Train alongside others in a class that keeps you motivated and accountable.'],
                        ['icon' => 'fa-paper-plane-o', 'title' => 'Personal Training', 'text' => 'One-on-one sessions tailored to your goals, with a trainer who knows your progress.'],
                        ['icon' => 'fa-bullseye', 'title' => 'Nutrition Coaching', 'text' => 'Guidance on eating right to match your training — because results start in the kitchen too.'],
                    ],
                ],
            ],
            [
                'type' => 'about', 'variant' => 'image-left',
                'content' => [
                    'heading' => 'Our Fitness Studio', 'body' => null,
                    'image_url' => 'site-templates/add-life-fitness/images/about.png',
                    'bullets' => [
                        ['text' => 'Aerobic'], ['text' => 'Cardio'], ['text' => 'Abdomen'],
                        ['text' => 'Special Trainer'], ['text' => 'Round the clock'],
                    ],
                ],
            ],
            [
                'type' => 'team', 'variant' => 'grid',
                'content' => [
                    'heading' => 'Our Trainers', 'subtext' => '',
                    'items' => [
                        ['photo_url' => 'site-templates/add-life-fitness/images/team/01.jpg', 'name' => 'Micky Deo', 'role' => 'Founder'],
                        ['photo_url' => 'site-templates/add-life-fitness/images/team/02.jpg', 'name' => 'Mike Timobbs', 'role' => 'Sr. Trainer'],
                        ['photo_url' => 'site-templates/add-life-fitness/images/team/03.jpg', 'name' => 'Remo Silvaus', 'role' => 'Sr. Trainer'],
                        ['photo_url' => 'site-templates/add-life-fitness/images/team/04.jpg', 'name' => 'Niscal Deon', 'role' => 'Jr. Trainer'],
                    ],
                ],
            ],
            [
                'type' => 'gallery', 'variant' => 'grid',
                'content' => [
                    'heading' => 'Gallery', 'subtext' => '',
                    'images' => [
                        ['url' => 'site-templates/add-life-fitness/images/portfolio/01.jpg', 'tags' => ['designing']],
                        ['url' => 'site-templates/add-life-fitness/images/portfolio/02.jpg', 'tags' => ['mobile', 'development']],
                        ['url' => 'site-templates/add-life-fitness/images/portfolio/03.jpg', 'tags' => ['designing']],
                        ['url' => 'site-templates/add-life-fitness/images/portfolio/04.jpg', 'tags' => ['mobile']],
                        ['url' => 'site-templates/add-life-fitness/images/portfolio/05.jpg', 'tags' => ['designing', 'development']],
                        ['url' => 'site-templates/add-life-fitness/images/portfolio/06.jpg', 'tags' => ['mobile']],
                        ['url' => 'site-templates/add-life-fitness/images/portfolio/07.jpg', 'tags' => ['designing', 'development']],
                        ['url' => 'site-templates/add-life-fitness/images/portfolio/08.jpg', 'tags' => ['mobile']],
                    ],
                    'filters' => [
                        ['key' => '*', 'label' => 'All Works'], ['key' => 'designing', 'label' => 'Designing'],
                        ['key' => 'mobile', 'label' => 'Mobile App'], ['key' => 'development', 'label' => 'Development'],
                    ],
                ],
            ],
            [
                'type' => 'pricing', 'variant' => 'cards',
                'content' => [
                    'heading' => 'Pricing', 'subtext' => '',
                    'items' => [
                        ['name' => 'Basic', 'price' => '45', 'period' => '/month', 'highlighted' => false, 'cta_text' => 'Get It Now!', 'cta_link' => '#contact-us',
                            'features' => [['text' => '1 Domain'], ['text' => '100GB Disk Space'], ['text' => 'Unlimited Bandwidth'], ['text' => 'Shared SSL Certificate'], ['text' => '10 Email Address'], ['text' => '24/7 Support']]],
                        ['name' => 'Bronze', 'price' => '85', 'period' => '/month', 'highlighted' => true, 'cta_text' => 'Get It Now!', 'cta_link' => '#contact-us',
                            'features' => [['text' => '5 Domain'], ['text' => '500GB Disk Space'], ['text' => 'Unlimited Bandwidth'], ['text' => 'Shared SSL Certificate'], ['text' => '30 Email Address'], ['text' => '24/7 Support']]],
                        ['name' => 'Silver', 'price' => '125', 'period' => '/month', 'highlighted' => false, 'cta_text' => 'Get It Now!', 'cta_link' => '#contact-us',
                            'features' => [['text' => '10 Domain'], ['text' => '2GB Disk Space'], ['text' => 'Unlimited Bandwidth'], ['text' => 'Shared SSL Certificate'], ['text' => '50 Email Address'], ['text' => '24/7 Support']]],
                        ['name' => 'Gold', 'price' => '185', 'period' => '/month', 'highlighted' => false, 'cta_text' => 'Get It Now!', 'cta_link' => '#contact-us',
                            'features' => [['text' => '15 Domain'], ['text' => '10GB Disk Space'], ['text' => 'Unlimited Bandwidth'], ['text' => 'Shared SSL Certificate'], ['text' => '100 Email Address'], ['text' => '24/7 Support']]],
                    ],
                ],
            ],
            [
                'type' => 'stats', 'variant' => 'row',
                'content' => [
                    'heading' => 'Healthy Report',
                    'items' => [
                        ['label' => 'Clients', 'value' => '6850', 'suffix' => ''],
                        ['label' => 'Trainers', 'value' => '1465', 'suffix' => ''],
                        ['label' => 'Programs', 'value' => '4325', 'suffix' => ''],
                        ['label' => 'Successes', 'value' => '2568', 'suffix' => ''],
                    ],
                ],
            ],
            [
                'type' => 'testimonials', 'variant' => 'grid',
                'content' => [
                    'heading' => 'Testimonial', 'subtext' => '',
                    'items' => [
                        ['quote' => "Six months in and I've never felt stronger. The trainers actually pay attention to your form, not just the clock.", 'name' => 'Thabo M.', 'role' => 'Member since 2024', 'avatar_url' => 'site-templates/add-life-fitness/images/pic1.jpg'],
                        ['quote' => "The group classes keep me showing up even on the days I don't feel like it. Best decision I've made this year.", 'name' => 'Sarah L.', 'role' => 'Member since 2023', 'avatar_url' => 'site-templates/add-life-fitness/images/pic2.jpg'],
                        ['quote' => "Great equipment, cleaner than any gym I've been to, and the staff genuinely want to see you improve.", 'name' => 'David R.', 'role' => 'Member since 2024', 'avatar_url' => 'site-templates/add-life-fitness/images/pic1.jpg'],
                    ],
                ],
            ],
            [
                'type' => 'contact', 'variant' => 'split',
                'content' => ['heading' => 'Contact Us', 'subtext' => ''],
            ],
        ],
    ],

    'aroma-beauty-spa' => [
        'sections' => [
            [
                'type' => 'hero', 'variant' => 'static',
                'content' => [
                    'headline' => 'Feel Beautiful, Every Day',
                    'subheadline' => 'A calm space to slow down and be looked after — book your next treatment today.',
                    'cta_text' => 'View Packages', 'cta_link' => '#pricing',
                    'background_image_url' => 'site-templates/aroma-beauty-spa/images/banner/banner.jpg',
                    'slides' => [],
                ],
            ],
            [
                'type' => 'services', 'variant' => 'cards',
                'content' => [
                    'heading' => "What's Best For You", 'subtext' => '',
                    'items' => [
                        ['icon' => 'fa-leaf', 'title' => 'Aroma Therapy', 'text' => 'Essential-oil treatments designed to ease tension and calm the mind.'],
                        ['icon' => 'fa-smile-o', 'title' => 'Facials', 'text' => 'Deep-cleansing facials tailored to your skin type, leaving you glowing.'],
                        ['icon' => 'fa-hand-paper-o', 'title' => 'Manicure', 'text' => 'Classic and gel manicures, finished exactly the way you like them.'],
                        ['icon' => 'fa-tint', 'title' => 'Body Spa', 'text' => 'Full-body treatments that combine massage, scrubs, and total relaxation.'],
                        ['icon' => 'fa-heartbeat', 'title' => 'Head Massage', 'text' => 'A tension-relieving head and scalp massage to unwind after a long week.'],
                        ['icon' => 'fa-magic', 'title' => 'Hair Spa', 'text' => 'Nourishing hair treatments that repair, hydrate, and restore shine.'],
                    ],
                ],
            ],
            [
                'type' => 'about', 'variant' => 'image-left',
                'content' => [
                    'heading' => 'Our Beauty Studio', 'body' => null,
                    'image_url' => 'site-templates/aroma-beauty-spa/images/about.png',
                    'bullets' => [
                        ['text' => 'Aroma Therapy'], ['text' => 'Manicure'], ['text' => 'Massage'],
                        ['text' => 'Body Spa'], ['text' => 'Hair Spa'],
                    ],
                ],
            ],
            [
                'type' => 'team', 'variant' => 'grid',
                'content' => [
                    'heading' => 'Our Team', 'subtext' => '',
                    'items' => [
                        ['photo_url' => 'site-templates/aroma-beauty-spa/images/team/01.jpg', 'name' => 'Micky Deo', 'role' => 'Founder'],
                        ['photo_url' => 'site-templates/aroma-beauty-spa/images/team/02.jpg', 'name' => 'Mike Timobbs', 'role' => 'Sr. Stylist'],
                        ['photo_url' => 'site-templates/aroma-beauty-spa/images/team/03.jpg', 'name' => 'Remo Silvaus', 'role' => 'Sr. Therapist'],
                        ['photo_url' => 'site-templates/aroma-beauty-spa/images/team/04.jpg', 'name' => 'Niscal Deon', 'role' => 'Massage Therapist'],
                    ],
                ],
            ],
            [
                'type' => 'gallery', 'variant' => 'grid',
                'content' => [
                    'heading' => 'Gallery', 'subtext' => '',
                    'images' => [
                        ['url' => 'site-templates/aroma-beauty-spa/images/portfolio/01.jpg', 'tags' => ['aroma']],
                        ['url' => 'site-templates/aroma-beauty-spa/images/portfolio/02.jpg', 'tags' => ['manicure', 'spa']],
                        ['url' => 'site-templates/aroma-beauty-spa/images/portfolio/03.jpg', 'tags' => ['aroma']],
                        ['url' => 'site-templates/aroma-beauty-spa/images/portfolio/04.jpg', 'tags' => ['manicure']],
                        ['url' => 'site-templates/aroma-beauty-spa/images/portfolio/05.jpg', 'tags' => ['aroma', 'spa']],
                        ['url' => 'site-templates/aroma-beauty-spa/images/portfolio/06.jpg', 'tags' => ['manicure']],
                        ['url' => 'site-templates/aroma-beauty-spa/images/portfolio/07.jpg', 'tags' => ['aroma', 'spa']],
                        ['url' => 'site-templates/aroma-beauty-spa/images/portfolio/08.jpg', 'tags' => ['manicure']],
                    ],
                    'filters' => [
                        ['key' => '*', 'label' => 'All'], ['key' => 'aroma', 'label' => 'Aroma'],
                        ['key' => 'manicure', 'label' => 'Manicure'], ['key' => 'spa', 'label' => 'Body Spa'],
                    ],
                ],
            ],
            [
                'type' => 'pricing', 'variant' => 'cards',
                'content' => [
                    'heading' => 'Pricing', 'subtext' => '',
                    'items' => [
                        ['name' => 'Essentials', 'price' => '45', 'period' => '/visit', 'highlighted' => false, 'cta_text' => 'Book Now', 'cta_link' => '#contact-us',
                            'features' => [['text' => '30-min facial'], ['text' => 'Express manicure'], ['text' => 'Head massage'], ['text' => 'Complimentary tea']]],
                        ['name' => 'Signature', 'price' => '85', 'period' => '/visit', 'highlighted' => true, 'cta_text' => 'Book Now', 'cta_link' => '#contact-us',
                            'features' => [['text' => '60-min facial'], ['text' => 'Gel manicure'], ['text' => 'Full body massage'], ['text' => 'Complimentary tea']]],
                        ['name' => 'Deluxe', 'price' => '125', 'period' => '/visit', 'highlighted' => false, 'cta_text' => 'Book Now', 'cta_link' => '#contact-us',
                            'features' => [['text' => '90-min facial'], ['text' => 'Gel manicure & pedicure'], ['text' => 'Aroma body spa'], ['text' => 'Complimentary tea & snacks']]],
                        ['name' => 'Ultimate', 'price' => '185', 'period' => '/visit', 'highlighted' => false, 'cta_text' => 'Book Now', 'cta_link' => '#contact-us',
                            'features' => [['text' => 'Full spa day'], ['text' => 'Mani, pedi & hair spa'], ['text' => 'Deep tissue massage'], ['text' => 'Private lounge access']]],
                    ],
                ],
            ],
            [
                'type' => 'stats', 'variant' => 'row',
                'content' => [
                    'heading' => 'A Studio People Trust',
                    'items' => [
                        ['label' => 'Happy Clients', 'value' => '3200', 'suffix' => ''],
                        ['label' => 'Therapists', 'value' => '12', 'suffix' => ''],
                        ['label' => 'Treatments', 'value' => '25', 'suffix' => ''],
                        ['label' => 'Years Open', 'value' => '8', 'suffix' => ''],
                    ],
                ],
            ],
            [
                'type' => 'testimonials', 'variant' => 'grid',
                'content' => [
                    'heading' => 'What Our Clients Say', 'subtext' => '',
                    'items' => [
                        ['quote' => 'The most relaxing hour of my week. My therapist always remembers exactly what pressure I like.', 'name' => 'Naledi P.', 'role' => 'Regular Client', 'avatar_url' => 'site-templates/aroma-beauty-spa/images/pic1.jpg'],
                        ['quote' => "Booked a facial on a whim and now I'm a monthly regular. My skin has never looked better.", 'name' => 'Chantelle W.', 'role' => 'Regular Client', 'avatar_url' => 'site-templates/aroma-beauty-spa/images/pic2.jpg'],
                        ['quote' => 'Clean, calm, and the staff made me feel completely at ease from the moment I walked in.', 'name' => 'Farah A.', 'role' => 'First-time Visitor', 'avatar_url' => 'site-templates/aroma-beauty-spa/images/pic1.jpg'],
                    ],
                ],
            ],
            [
                'type' => 'contact', 'variant' => 'split',
                'content' => ['heading' => 'Book Your Visit', 'subtext' => ''],
            ],
        ],
    ],

    'beauty-salon' => [
        'sections' => [
            [
                'type' => 'hero', 'variant' => 'carousel',
                'content' => [
                    'headline' => 'Hair, Reinvented',
                    'subheadline' => 'Cut, colour, and style — crafted around what actually suits you',
                    'cta_text' => 'Book an Appointment', 'cta_link' => '#contact',
                    'background_image_url' => 'site-templates/beauty-salon/images/banner/banner.jpg',
                    'slides' => [
                        ['image_url' => 'site-templates/beauty-salon/images/banner/banner.jpg', 'headline' => 'Hair, Reinvented', 'subheadline' => 'Cut, colour, and style — crafted around what actually suits you'],
                        ['image_url' => 'site-templates/beauty-salon/images/portfolio/02.jpg', 'headline' => 'Colour That Turns Heads', 'subheadline' => 'From subtle balayage to bold transformations'],
                        ['image_url' => 'site-templates/beauty-salon/images/portfolio/05.jpg', 'headline' => 'Styled For Every Occasion', 'subheadline' => "Bridal, events, or just a Tuesday — we've got you"],
                    ],
                ],
            ],
            [
                'type' => 'about', 'variant' => 'image-left',
                'content' => [
                    'heading' => 'Trending Styles, Trusted Hands', 'body' => null,
                    'image_url' => 'site-templates/beauty-salon/images/about.png',
                    'bullets' => [
                        ['text' => 'Precision cutting'], ['text' => 'Colour & balayage'], ['text' => 'Keratin treatments'],
                        ['text' => 'Bridal styling'], ['text' => 'Consultations included'],
                    ],
                ],
            ],
            [
                'type' => 'services', 'variant' => 'cards',
                'content' => [
                    'heading' => 'Our Services',
                    'subtext' => 'Every visit starts with a consultation, so what you get is always what you actually wanted.',
                    'items' => [
                        ['icon' => 'fa-cut', 'title' => 'Cuts & Styling', 'text' => 'Precision cuts and blowouts tailored to your face shape and hair type.'],
                        ['icon' => 'fa-tint', 'title' => 'Colour & Balayage', 'text' => 'From your first colour to a full transformation, done gently and evenly.'],
                        ['icon' => 'fa-magic', 'title' => 'Treatments', 'text' => 'Keratin smoothing, deep conditioning, and repair treatments that last.'],
                        ['icon' => 'fa-heart', 'title' => 'Bridal & Events', 'text' => 'Trial run included — so your big day looks exactly how you pictured it.'],
                    ],
                ],
            ],
            [
                'type' => 'team', 'variant' => 'grid',
                'content' => [
                    'heading' => 'Meet the Team', 'subtext' => '',
                    'items' => [
                        ['photo_url' => 'site-templates/beauty-salon/images/team/01.jpg', 'name' => 'Lerato M.', 'role' => 'Senior Stylist'],
                        ['photo_url' => 'site-templates/beauty-salon/images/team/02.jpg', 'name' => 'Thandiwe K.', 'role' => 'Colour Specialist'],
                        ['photo_url' => 'site-templates/beauty-salon/images/team/03.jpg', 'name' => 'Amara N.', 'role' => 'Stylist'],
                        ['photo_url' => 'site-templates/beauty-salon/images/team/04.jpg', 'name' => 'Zanele R.', 'role' => 'Salon Manager'],
                    ],
                ],
            ],
            [
                'type' => 'gallery', 'variant' => 'grid',
                'content' => [
                    'heading' => 'Gallery', 'subtext' => '',
                    'images' => [
                        ['url' => 'site-templates/beauty-salon/images/portfolio/01.jpg', 'tags' => ['cuts']],
                        ['url' => 'site-templates/beauty-salon/images/portfolio/02.jpg', 'tags' => ['colour']],
                        ['url' => 'site-templates/beauty-salon/images/portfolio/03.jpg', 'tags' => ['cuts', 'styling']],
                        ['url' => 'site-templates/beauty-salon/images/portfolio/04.jpg', 'tags' => ['bridal']],
                        ['url' => 'site-templates/beauty-salon/images/portfolio/05.jpg', 'tags' => ['styling', 'bridal']],
                        ['url' => 'site-templates/beauty-salon/images/portfolio/06.jpg', 'tags' => ['colour']],
                        ['url' => 'site-templates/beauty-salon/images/portfolio/07.jpg', 'tags' => ['cuts']],
                        ['url' => 'site-templates/beauty-salon/images/portfolio/08.jpg', 'tags' => ['styling']],
                    ],
                    'filters' => [
                        ['key' => '*', 'label' => 'All'], ['key' => 'cuts', 'label' => 'Cuts'],
                        ['key' => 'colour', 'label' => 'Colour'], ['key' => 'styling', 'label' => 'Styling'], ['key' => 'bridal', 'label' => 'Bridal'],
                    ],
                ],
            ],
            [
                'type' => 'testimonials', 'variant' => 'carousel',
                'content' => [
                    'heading' => 'What Clients Say', 'subtext' => '',
                    'items' => [
                        ['quote' => "First time I've walked out of a salon and actually loved my colour straight away. No more guessing games.", 'name' => 'Palesa D.', 'role' => '', 'avatar_url' => null, 'initials' => 'PD'],
                        ['quote' => 'Booked for my wedding morning and they made the whole team look incredible, on time, no stress.', 'name' => 'Michelle A.', 'role' => '', 'avatar_url' => null, 'initials' => 'MA'],
                        ['quote' => 'My curls have never been healthier. They actually listen before they cut.', 'name' => 'Refilwe S.', 'role' => '', 'avatar_url' => null, 'initials' => 'RS'],
                    ],
                ],
            ],
            [
                'type' => 'contact', 'variant' => 'split',
                'content' => ['heading' => 'Book Your Visit', 'subtext' => ''],
            ],
        ],
    ],

    'lovely-wedding' => [
        'sections' => [
            [
                'type' => 'hero', 'variant' => 'static',
                'content' => [
                    'eyebrow' => 'Wedding & Event Planning',
                    'headline' => null,
                    'subheadline' => "We plan the day you've been dreaming of, down to the last detail.",
                    'cta_text' => 'Book a Consultation', 'cta_link' => '#contact',
                    'background_image_url' => 'site-templates/lovely-wedding/img/main2.jpg',
                    'slides' => [],
                ],
            ],
            [
                'type' => 'about', 'variant' => 'image-left',
                'content' => [
                    'heading' => 'About Us', 'body' => null,
                    'image_url' => 'site-templates/lovely-wedding/img/about2.png',
                    'bullets' => [
                        ['text' => 'Full wedding planning'], ['text' => 'Day-of coordination'], ['text' => 'Venue sourcing'],
                        ['text' => 'Vendor management'], ['text' => 'Bridal styling'],
                    ],
                ],
            ],
            [
                'type' => 'services', 'variant' => 'cards',
                'content' => [
                    'heading' => 'Our Packages',
                    'subtext' => 'From an intimate ceremony to a full weekend of celebrations, we tailor every package to you.',
                    'items' => [
                        ['icon' => 'fa-glass', 'title' => 'Engagement Party', 'text' => 'A relaxed celebration to mark the beginning of your journey together.'],
                        ['icon' => 'fa-institution', 'title' => 'Full Wedding', 'text' => 'End-to-end planning — venue, vendors, styling, and timeline, fully managed.'],
                        ['icon' => 'fa-cutlery', 'title' => 'Reception Dinner', 'text' => 'A reception that feels as good as it looks, from seating to the last dance.'],
                        ['icon' => 'fa-heart', 'title' => 'Ceremony Coordination', 'text' => 'On-the-day coordination so you can be fully present for your own wedding.'],
                    ],
                ],
            ],
            [
                'type' => 'gallery', 'variant' => 'grid',
                'content' => [
                    'heading' => 'Photo Gallery',
                    'subtext' => "Moments from weddings we've had the privilege of planning.",
                    'images' => collect(range(1, 8))->map(fn ($n) => ['url' => "site-templates/lovely-wedding/img/portfolio_pic{$n}.jpg", 'tags' => []])->all(),
                    'filters' => [],
                ],
            ],
            [
                'type' => 'team', 'variant' => 'circles',
                'content' => [
                    'heading' => 'Our Team',
                    'subtext' => 'The people who bring every detail of your day together.',
                    'items' => [
                        ['photo_url' => 'site-templates/lovely-wedding/img/team01.jpg', 'name' => 'Rosy Illue', 'role' => 'Lead Planner'],
                        ['photo_url' => 'site-templates/lovely-wedding/img/team02.jpg', 'name' => 'Chrislke Moyo', 'role' => 'Floral Designer'],
                        ['photo_url' => 'site-templates/lovely-wedding/img/team03.jpg', 'name' => 'Mike Reiln', 'role' => 'Day-of Coordinator'],
                        ['photo_url' => 'site-templates/lovely-wedding/img/team04.jpg', 'name' => 'Dennisel Cruz', 'role' => 'Bridal Stylist'],
                    ],
                ],
            ],
            [
                'type' => 'contact', 'variant' => 'split',
                'content' => ['heading' => 'Contact Us', 'subtext' => "Tell us a little about your day and we'll be in touch."],
            ],
        ],
    ],

];
