<?php

namespace App\Http\Controllers;

use App\Models\PublicLaunch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicLaunchController extends Controller
{
    public function show(string $key): View
    {
        $launch = PublicLaunch::where('key', $key)->where('is_active', true)->firstOrFail();
        $launch->load('publishedQuestions');

        return view('public-launch.show', compact('launch'));
    }

    public function askQuestion(Request $request, string $key): RedirectResponse
    {
        $launch = PublicLaunch::where('key', $key)->where('is_active', true)->firstOrFail();

        abort_unless($launch->qa_enabled, 404);

        $data = $request->validate([
            'asker_name'  => 'nullable|string|max:255',
            'asker_email' => 'nullable|email|max:255',
            'question'    => 'required|string|max:2000',
        ]);

        $launch->questions()->create($data);

        return back()->with('success', "Thanks — we'll answer this soon.");
    }
}
