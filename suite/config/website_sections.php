<?php

// The fixed catalog of page-builder section types. Not tenant-definable —
// this is the developer-owned schema each TenantPageSection.type must match.
// Field `type` is one of: text | textarea | url | select | image | repeater.
return [

    'hero' => [
        'label'           => 'Hero',
        'variants'        => ['carousel' => 'Carousel', 'static' => 'Static Image'],
        'default_variant' => 'carousel',
        'fields'          => [
            ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow (small text above headline)', 'max' => 60],
            ['key' => 'headline', 'type' => 'text', 'label' => 'Headline', 'max' => 120],
            ['key' => 'subheadline', 'type' => 'textarea', 'label' => 'Subheadline', 'max' => 300],
            ['key' => 'cta_text', 'type' => 'text', 'label' => 'Button text', 'max' => 40],
            ['key' => 'cta_link', 'type' => 'text', 'label' => 'Button link', 'max' => 255],
            ['key' => 'background_image_url', 'type' => 'image', 'label' => 'Background image'],
            ['key' => 'slides', 'type' => 'repeater', 'label' => 'Slides (carousel only)', 'max_items' => 5, 'item_fields' => [
                ['key' => 'image_url', 'type' => 'image', 'label' => 'Image'],
                ['key' => 'headline', 'type' => 'text', 'label' => 'Headline'],
                ['key' => 'subheadline', 'type' => 'text', 'label' => 'Subheadline'],
            ]],
        ],
        'default_content' => [
            'eyebrow' => null,
            'headline' => null,
            'subheadline' => 'Tell visitors what you do best.',
            'cta_text' => 'Get in Touch', 'cta_link' => '#contact',
            'background_image_url' => null, 'slides' => [],
        ],
    ],

    'about' => [
        'label'           => 'About',
        'variants'        => ['image-left' => 'Image Left', 'image-right' => 'Image Right'],
        'default_variant' => 'image-left',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'body', 'type' => 'textarea', 'label' => 'Body text', 'max' => 1000],
            ['key' => 'image_url', 'type' => 'image', 'label' => 'Image'],
            ['key' => 'bullets', 'type' => 'repeater', 'label' => 'Bullet points', 'max_items' => 8, 'item_fields' => [
                ['key' => 'text', 'type' => 'text', 'label' => 'Point'],
            ]],
        ],
        'default_content' => [
            'heading' => 'Who We Are',
            'body' => 'Tell your customers about your business — add a description in the editor.',
            'image_url' => null, 'bullets' => [],
        ],
    ],

    'services' => [
        'label'           => 'Services / Features',
        'variants'        => ['cards' => 'Cards', 'list' => 'List'],
        'default_variant' => 'cards',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'subtext', 'type' => 'textarea', 'label' => 'Subtext', 'max' => 300],
            ['key' => 'items', 'type' => 'repeater', 'label' => 'Items', 'max_items' => 12, 'item_fields' => [
                ['key' => 'icon', 'type' => 'text', 'label' => 'Icon (Font Awesome class, e.g. fa-heart)'],
                ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                ['key' => 'text', 'type' => 'textarea', 'label' => 'Description'],
            ]],
        ],
        'default_content' => ['heading' => 'What We Offer', 'subtext' => '', 'items' => []],
    ],

    'gallery' => [
        'label'           => 'Gallery',
        'variants'        => ['grid' => 'Grid', 'masonry' => 'Masonry'],
        'default_variant' => 'grid',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'subtext', 'type' => 'textarea', 'label' => 'Subtext', 'max' => 300],
            ['key' => 'images', 'type' => 'repeater', 'label' => 'Photos', 'max_items' => 24, 'item_fields' => [
                ['key' => 'url', 'type' => 'image', 'label' => 'Photo'],
                ['key' => 'tags', 'type' => 'text', 'label' => 'Tags (comma separated, optional)'],
            ]],
        ],
        'default_content' => ['heading' => 'Our Gallery', 'subtext' => '', 'images' => [], 'filters' => []],
    ],

    'testimonials' => [
        'label'           => 'Testimonials',
        'variants'        => ['carousel' => 'Carousel', 'grid' => 'Grid'],
        'default_variant' => 'carousel',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'subtext', 'type' => 'textarea', 'label' => 'Subtext', 'max' => 300],
            ['key' => 'items', 'type' => 'repeater', 'label' => 'Quotes', 'max_items' => 12, 'item_fields' => [
                ['key' => 'quote', 'type' => 'textarea', 'label' => 'Quote'],
                ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
                ['key' => 'role', 'type' => 'text', 'label' => 'Role / context'],
                ['key' => 'avatar_url', 'type' => 'image', 'label' => 'Photo (optional)'],
            ]],
        ],
        'default_content' => ['heading' => 'What Our Clients Say', 'subtext' => '', 'items' => []],
    ],

    'pricing' => [
        'label'           => 'Pricing',
        'variants'        => ['cards' => 'Cards', 'table' => 'Table'],
        'default_variant' => 'cards',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'subtext', 'type' => 'textarea', 'label' => 'Subtext', 'max' => 300],
            ['key' => 'items', 'type' => 'repeater', 'label' => 'Plans', 'max_items' => 6, 'item_fields' => [
                ['key' => 'name', 'type' => 'text', 'label' => 'Plan name'],
                ['key' => 'price', 'type' => 'text', 'label' => 'Price'],
                ['key' => 'period', 'type' => 'text', 'label' => 'Period (e.g. /month)'],
                ['key' => 'features', 'type' => 'repeater', 'label' => 'Features', 'item_fields' => [
                    ['key' => 'text', 'type' => 'text', 'label' => 'Feature'],
                ]],
                ['key' => 'highlighted', 'type' => 'select', 'label' => 'Highlighted', 'options' => ['0' => 'No', '1' => 'Yes']],
                ['key' => 'cta_text', 'type' => 'text', 'label' => 'Button text'],
                ['key' => 'cta_link', 'type' => 'text', 'label' => 'Button link'],
            ]],
        ],
        'default_content' => ['heading' => 'Pricing', 'subtext' => '', 'items' => []],
    ],

    'faq' => [
        'label'           => 'FAQ',
        'variants'        => ['accordion' => 'Accordion', 'two-column' => 'Two Column'],
        'default_variant' => 'accordion',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'subtext', 'type' => 'textarea', 'label' => 'Subtext', 'max' => 300],
            ['key' => 'items', 'type' => 'repeater', 'label' => 'Questions', 'max_items' => 20, 'item_fields' => [
                ['key' => 'question', 'type' => 'text', 'label' => 'Question'],
                ['key' => 'answer', 'type' => 'textarea', 'label' => 'Answer'],
            ]],
        ],
        'default_content' => ['heading' => 'Frequently Asked Questions', 'subtext' => '', 'items' => []],
    ],

    'map' => [
        'label'           => 'Map',
        'variants'        => ['embed' => 'Full Width', 'split' => 'Split with Address'],
        'default_variant' => 'embed',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'address', 'type' => 'textarea', 'label' => 'Address', 'max' => 300],
            ['key' => 'embed_url', 'type' => 'url', 'label' => 'Google Maps embed URL'],
        ],
        'default_content' => ['heading' => 'Find Us', 'address' => null, 'embed_url' => null, 'lat' => null, 'lng' => null],
    ],

    'booking_form' => [
        'label'           => 'Booking Form',
        'variants'        => ['inline' => 'Inline', 'card' => 'Card'],
        'default_variant' => 'inline',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'subtext', 'type' => 'textarea', 'label' => 'Subtext', 'max' => 300],
        ],
        'default_content' => ['heading' => 'Book an Appointment', 'subtext' => ''],
    ],

    'product_grid' => [
        'label'           => 'Product Grid',
        'variants'        => ['grid' => 'Grid', 'carousel' => 'Carousel'],
        'default_variant' => 'grid',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'subtext', 'type' => 'textarea', 'label' => 'Subtext', 'max' => 300],
            ['key' => 'source', 'type' => 'select', 'label' => 'Source', 'options' => ['featured' => 'Featured', 'latest' => 'Latest', 'manual' => 'Manual selection']],
            ['key' => 'limit', 'type' => 'text', 'label' => 'Max products to show'],
        ],
        'default_content' => ['heading' => 'Shop Our Products', 'subtext' => '', 'source' => 'featured', 'product_ids' => [], 'limit' => 8],
    ],

    'video' => [
        'label'           => 'Video',
        'variants'        => ['full-width' => 'Full Width', 'framed' => 'Framed'],
        'default_variant' => 'framed',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'video_url', 'type' => 'url', 'label' => 'Video URL (YouTube/Vimeo embed or MP4)'],
            ['key' => 'poster_image_url', 'type' => 'image', 'label' => 'Poster image'],
            ['key' => 'caption', 'type' => 'text', 'label' => 'Caption'],
        ],
        'default_content' => ['heading' => null, 'video_url' => null, 'poster_image_url' => null, 'caption' => null],
    ],

    'instagram_feed' => [
        'label'           => 'Instagram Feed',
        'variants'        => ['grid-3' => '3 Columns', 'grid-4' => '4 Columns'],
        'default_variant' => 'grid-4',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'handle', 'type' => 'text', 'label' => 'Instagram handle (without @)'],
            ['key' => 'images', 'type' => 'repeater', 'label' => 'Photos', 'max_items' => 12, 'item_fields' => [
                ['key' => 'url', 'type' => 'image', 'label' => 'Photo'],
            ]],
        ],
        'default_content' => ['heading' => 'Follow Us', 'handle' => null, 'images' => []],
    ],

    'newsletter' => [
        'label'           => 'Newsletter',
        'variants'        => ['inline-bar' => 'Inline Bar', 'centered-card' => 'Centered Card'],
        'default_variant' => 'centered-card',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'subtext', 'type' => 'textarea', 'label' => 'Subtext', 'max' => 300],
        ],
        'default_content' => ['heading' => 'Stay in the Loop', 'subtext' => 'Get updates straight to your inbox.'],
    ],

    'contact' => [
        'label'           => 'Contact',
        'variants'        => ['split' => 'Split', 'form-only' => 'Form Only'],
        'default_variant' => 'split',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'subtext', 'type' => 'textarea', 'label' => 'Subtext', 'max' => 300],
            ['key' => 'email', 'type' => 'text', 'label' => 'Email'],
            ['key' => 'phone', 'type' => 'text', 'label' => 'Phone'],
            ['key' => 'whatsapp_number', 'type' => 'text', 'label' => 'WhatsApp number'],
            ['key' => 'address', 'type' => 'textarea', 'label' => 'Address'],
        ],
        'default_content' => [
            'heading' => 'Contact Us', 'subtext' => '', 'email' => null, 'phone' => null,
            'whatsapp_number' => null, 'address' => null,
            'business_hours' => [], 'socials' => [],
        ],
    ],

    'reviews' => [
        'label'           => 'Reviews',
        'variants'        => ['cards' => 'Cards', 'carousel' => 'Carousel'],
        'default_variant' => 'cards',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'subtext', 'type' => 'textarea', 'label' => 'Subtext', 'max' => 300],
            ['key' => 'items', 'type' => 'repeater', 'label' => 'Reviews', 'max_items' => 20, 'item_fields' => [
                ['key' => 'author_name', 'type' => 'text', 'label' => 'Name'],
                ['key' => 'rating', 'type' => 'select', 'label' => 'Rating', 'options' => ['5' => '5 stars', '4' => '4 stars', '3' => '3 stars', '2' => '2 stars', '1' => '1 star']],
                ['key' => 'body', 'type' => 'textarea', 'label' => 'Review text'],
                ['key' => 'source', 'type' => 'text', 'label' => 'Source (e.g. Google, Facebook)'],
            ]],
        ],
        'default_content' => ['heading' => 'Customer Reviews', 'subtext' => '', 'items' => []],
    ],

    'team' => [
        'label'           => 'Team',
        'variants'        => ['grid' => 'Grid', 'circles' => 'Circular Photos'],
        'default_variant' => 'grid',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'subtext', 'type' => 'textarea', 'label' => 'Subtext', 'max' => 300],
            ['key' => 'items', 'type' => 'repeater', 'label' => 'Team members', 'max_items' => 12, 'item_fields' => [
                ['key' => 'photo_url', 'type' => 'image', 'label' => 'Photo'],
                ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
                ['key' => 'role', 'type' => 'text', 'label' => 'Role'],
            ]],
        ],
        'default_content' => ['heading' => 'Meet the Team', 'subtext' => '', 'items' => []],
    ],

    'stats' => [
        'label'           => 'Stats',
        'variants'        => ['row' => 'Row', 'boxed' => 'Boxed'],
        'default_variant' => 'row',
        'fields'          => [
            ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'max' => 80],
            ['key' => 'items', 'type' => 'repeater', 'label' => 'Stats', 'max_items' => 8, 'item_fields' => [
                ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                ['key' => 'value', 'type' => 'text', 'label' => 'Value (number)'],
                ['key' => 'suffix', 'type' => 'text', 'label' => 'Suffix (e.g. +, %)'],
            ]],
        ],
        'default_content' => ['heading' => null, 'items' => []],
    ],

];
