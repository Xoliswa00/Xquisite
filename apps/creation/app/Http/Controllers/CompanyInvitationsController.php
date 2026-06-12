<?php

namespace App\Http\Controllers;

use App\Models\company_invitations;
use App\Http\Requests\Storecompany_invitationsRequest;
use App\Http\Requests\Updatecompany_invitationsRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class CompanyInvitationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
 public function store(Request $request, Company $company)
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:owner,admin,finance,viewer',
        ]);

        $invitation = company_invitations::create([
            'company_id' => $company->id,
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
            'token' => Str::uuid(),
            'expires_at' => now()->addDays(7),
        ]);

        // Send email (basic example)
        Mail::raw(
            "You have been invited to join {$company->name}. 
             Accept here: " . url("/invitations/accept/{$invitation->token}"),
            function ($message) use ($validated) {
                $message->to($validated['email'])
                        ->subject('Company Invitation');
            }
        );

        return response()->json($invitation, 201);
    }
     public function accept($token)
    {
        $invitation = company_invitations::where('token', $token)->firstOrFail();

        if ($invitation->isExpired()) {
            return response()->json(['message' => 'Invitation expired'], 410);
        }

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Please register or login first'
            ], 401);
        }

        if ($user->email !== $invitation->email) {
            return response()->json([
                'message' => 'This invitation was sent to another email address'
            ], 403);
        }

        $invitation->company->users()->attach($user->id, [
            'role' => $invitation->role
        ]);

        $invitation->delete();

        return response()->json([
            'message' => 'Successfully joined company'
        ]);
    }
}
