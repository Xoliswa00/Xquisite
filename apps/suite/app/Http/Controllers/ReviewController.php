<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewPrompt;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'rating'            => 'required|integer|min:1|max:5',
            'title'             => 'nullable|string|max:120',
            'body'              => 'required|string|min:10|max:1000',
            'display_name'      => 'nullable|string|max:80',
            'threshold'         => 'nullable|integer',
        ]);

        $user   = auth()->user();
        $tenant = $user?->tenant;

        $review = Review::create([
            'user_id'           => $user?->id,
            'tenant_id'         => $tenant?->id,
            'rating'            => $data['rating'],
            'title'             => $data['title'] ?? null,
            'body'              => $data['body'],
            'display_name'      => $data['display_name'] ?? ($tenant?->name),
            'business_type'     => $tenant?->industry,
            'status'            => 'pending',
            'prompted_at_count' => $data['threshold'] ?? null,
        ]);

        // Mark the prompt as converted so it doesn't fire again
        if ($data['threshold'] ?? null) {
            ReviewPrompt::where('user_id', $user?->id)
                ->where('threshold', $data['threshold'])
                ->update(['review_id' => $review->id]);
        }

        return back()->with('success', 'Thank you for your feedback! It will be reviewed before being published.');
    }

    public function dismiss(Request $request)
    {
        $threshold = $request->validate(['threshold' => 'required|integer'])['threshold'];

        ReviewPrompt::where('user_id', auth()->id())
            ->where('threshold', $threshold)
            ->update(['dismissed_at' => now()]);

        return response()->json(['dismissed' => true]);
    }
}
