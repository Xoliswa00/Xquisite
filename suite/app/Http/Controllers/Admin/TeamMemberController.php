<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamMemberController extends Controller
{
    public function index()
    {
        $members = TeamMember::ordered()->get();

        return view('admin.team-members.index', compact('members'));
    }

    public function create()
    {
        return view('admin.team-members.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'role'         => 'required|string|max:100',
            'bio'          => 'nullable|string|max:500',
            'photo'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'linkedin_url' => 'nullable|url|max:255',
            'sort_order'   => 'nullable|integer|min:0',
            'is_active'    => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('team', 'public');
        }

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        TeamMember::create($data);

        return redirect()->route('admin.team-members.index')
            ->with('success', "{$data['name']} added to the team.");
    }

    public function edit(TeamMember $teamMember)
    {
        return view('admin.team-members.edit', compact('teamMember'));
    }

    public function update(Request $request, TeamMember $teamMember)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'role'         => 'required|string|max:100',
            'bio'          => 'nullable|string|max:500',
            'photo'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'linkedin_url' => 'nullable|url|max:255',
            'sort_order'   => 'nullable|integer|min:0',
            'is_active'    => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            if ($teamMember->photo) {
                Storage::disk('public')->delete($teamMember->photo);
            }
            $data['photo'] = $request->file('photo')->store('team', 'public');
        }

        $data['is_active']  = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? $teamMember->sort_order;

        $teamMember->update($data);

        return redirect()->route('admin.team-members.index')
            ->with('success', "{$teamMember->name} updated.");
    }

    public function destroy(TeamMember $teamMember)
    {
        if ($teamMember->photo) {
            Storage::disk('public')->delete($teamMember->photo);
        }

        $name = $teamMember->name;
        $teamMember->delete();

        return redirect()->route('admin.team-members.index')
            ->with('success', "{$name} removed from the team.");
    }
}
