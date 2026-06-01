<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            [
                'rating'        => 5,
                'title'         => 'Transformed how we run the salon',
                'body'          => 'Before Xquisite we were using WhatsApp for bookings and a spreadsheet for stock. Now everything is in one place — bookings, POS, staff schedules. Our no-show rate dropped by 40% in the first month.',
                'display_name'  => 'Lebo\'s Hair Lounge',
                'business_type' => 'Hair & Beauty',
                'status'        => 'approved',
                'is_featured'   => true,
            ],
            [
                'rating'        => 5,
                'title'         => 'Finally a system built for South African businesses',
                'body'          => 'Everything works in rands, PayFast is built in, and the support actually responds. We use the booking and POS modules daily. Worth every cent.',
                'display_name'  => 'Mpho\'s Wellness Studio',
                'business_type' => 'Wellness & Fitness',
                'status'        => 'approved',
                'is_featured'   => true,
            ],
            [
                'rating'        => 5,
                'title'         => 'Property management made simple',
                'body'          => 'Managing 12 units used to mean endless spreadsheets and chasing renters on WhatsApp. The renter portal alone saved me hours a week — tenants log maintenance requests and view their lease themselves.',
                'display_name'  => 'Sandton Property Group',
                'business_type' => 'Property Management',
                'status'        => 'approved',
                'is_featured'   => false,
            ],
            [
                'rating'        => 4,
                'title'         => 'Great system, keeps getting better',
                'body'          => 'We\'ve been on the platform for three months. The booking module is rock solid and the analytics give us visibility we never had before. Looking forward to the loyalty module.',
                'display_name'  => 'Cape Town Auto Spares',
                'business_type' => 'Retail',
                'status'        => 'approved',
                'is_featured'   => false,
            ],
            [
                'rating'        => 5,
                'title'         => 'Our whole team uses it every day',
                'body'          => 'From the front desk booking appointments to the stockroom managing inventory — everyone is on one system now. The setup took less than a day and the demo showed us exactly what we were getting.',
                'display_name'  => 'Bella Med Aesthetics',
                'business_type' => 'Medical Aesthetics',
                'status'        => 'approved',
                'is_featured'   => true,
            ],
        ];

        foreach ($reviews as $data) {
            Review::firstOrCreate(
                ['display_name' => $data['display_name'], 'title' => $data['title']],
                $data
            );
        }
    }
}
