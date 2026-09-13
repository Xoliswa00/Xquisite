<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutreachCampaign;
use Illuminate\Http\Request;

class OutreachCampaignController extends Controller
{
    public function index()
    {
        $campaigns = OutreachCampaign::withCount('applications')
            ->latest()
            ->get();

        return view('admin.outreach-campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('admin.outreach-campaigns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'nullable|string|max:100',
            'target_business_type' => 'nullable|in:salon,beauty,wellness,fitness,service,other',
            'planned_count' => 'nullable|integer|min:1',
            'status' => 'required|in:planned,active,completed',
            'started_at' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $campaign = OutreachCampaign::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.outreach-campaigns.show', $campaign)->with('success', "Campaign '{$campaign->name}' created.");
    }

    public function show(OutreachCampaign $outreachCampaign)
    {
        $outreachCampaign->load(['applications' => fn ($q) => $q->latest('score')]);

        $breakdown = $outreachCampaign->industryBreakdown();

        return view('admin.outreach-campaigns.show', ['campaign' => $outreachCampaign, 'breakdown' => $breakdown]);
    }
}
