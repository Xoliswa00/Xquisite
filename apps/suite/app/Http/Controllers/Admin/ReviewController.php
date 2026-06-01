<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with('tenant')
            ->latest()
            ->limit(200)
            ->get()
            ->groupBy('status');

        return view('admin.reviews.index', compact('reviews'));
    }

    public function updateStatus(Request $request, Review $review)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $review->update(['status' => $request->status]);

        return back()->with('success', "Review {$request->status}.");
    }

    public function toggleFeatured(Review $review)
    {
        $review->update(['is_featured' => ! $review->is_featured]);

        return back()->with('success', $review->is_featured ? 'Review featured.' : 'Review unfeatured.');
    }
}
