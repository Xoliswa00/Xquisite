<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicLaunch;
use App\Models\PublicQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicLaunchController extends Controller
{
    public function index(): View
    {
        $launches = PublicLaunch::withCount([
            'questions',
            'questions as unanswered_count' => fn ($q) => $q->whereNull('answer'),
        ])->latest()->get();

        return view('admin.public-launches.index', compact('launches'));
    }

    public function edit(PublicLaunch $publicLaunch): View
    {
        return view('admin.public-launches.edit', ['launch' => $publicLaunch]);
    }

    public function update(Request $request, PublicLaunch $publicLaunch): RedirectResponse
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'tagline'    => 'nullable|string|max:255',
            'benefits'   => 'nullable|string|max:4000',
            'launch_at'  => 'nullable|date',
            'is_active'  => 'nullable|boolean',
            'qa_enabled' => 'nullable|boolean',
        ]);

        $benefits = collect(explode("\n", $data['benefits'] ?? ''))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $publicLaunch->update([
            'title'      => $data['title'],
            'tagline'    => $data['tagline'] ?? null,
            'benefits'   => $benefits,
            'launch_at'  => $data['launch_at'] ?? null,
            'is_active'  => $request->boolean('is_active'),
            'qa_enabled' => $request->boolean('qa_enabled'),
        ]);

        return redirect()->route('admin.public-launches.index')->with('success', 'Launch page updated.');
    }

    public function questions(PublicLaunch $publicLaunch): View
    {
        $questions = $publicLaunch->questions()->latest()->get()->groupBy(
            fn (PublicQuestion $q) => $q->isAnswered() ? 'answered' : 'pending'
        );

        return view('admin.public-launches.questions', ['launch' => $publicLaunch, 'questions' => $questions]);
    }

    public function answerQuestion(Request $request, PublicLaunch $publicLaunch, PublicQuestion $publicQuestion): RedirectResponse
    {
        abort_unless($publicQuestion->public_launch_id === $publicLaunch->id, 404);

        $data = $request->validate([
            'answer'       => 'required|string|max:4000',
            'is_published' => 'nullable|boolean',
        ]);

        $publicQuestion->update([
            'answer'       => $data['answer'],
            'answered_at'  => now(),
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('success', 'Answer saved.');
    }

    public function togglePublished(PublicLaunch $publicLaunch, PublicQuestion $publicQuestion): RedirectResponse
    {
        abort_unless($publicQuestion->public_launch_id === $publicLaunch->id, 404);
        abort_unless($publicQuestion->isAnswered(), 422);

        $publicQuestion->update(['is_published' => ! $publicQuestion->is_published]);

        return back()->with('success', $publicQuestion->is_published ? 'Question published.' : 'Question unpublished.');
    }

    public function destroyQuestion(PublicLaunch $publicLaunch, PublicQuestion $publicQuestion): RedirectResponse
    {
        abort_unless($publicQuestion->public_launch_id === $publicLaunch->id, 404);

        $publicQuestion->delete();

        return back()->with('success', 'Question removed.');
    }
}
