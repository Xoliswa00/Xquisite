<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplateReview;
use Illuminate\Http\Request;

class TemplateReviewController extends Controller
{
    public function index()
    {
        $reviews = TemplateReview::with(['tenant', 'template'])
            ->latest()
            ->limit(200)
            ->get()
            ->groupBy('status');

        return view('admin.template-reviews.index', compact('reviews'));
    }

    public function updateStatus(Request $request, TemplateReview $templateReview)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $templateReview->update(['status' => $request->status]);

        $template = Template::where('key', $templateReview->template_key)->first();
        $template?->recomputeRating();

        return back()->with('success', "Review {$request->status}.");
    }
}
