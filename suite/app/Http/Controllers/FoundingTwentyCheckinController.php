<?php

namespace App\Http\Controllers;

use App\Models\FoundingTwentyCheckin;
use Illuminate\Http\Request;

class FoundingTwentyCheckinController extends Controller
{
    public function show(FoundingTwentyCheckin $checkin, string $token)
    {
        abort_unless(hash_equals($checkin->checkinToken(), $token), 403, 'Invalid or expired link.');

        return view('founding-twenty.checkin', ['checkin' => $checkin, 'token' => $token]);
    }

    public function store(Request $request, FoundingTwentyCheckin $checkin, string $token)
    {
        abort_unless(hash_equals($checkin->checkinToken(), $token), 403, 'Invalid or expired link.');
        abort_if($checkin->isComplete(), 422, 'This check-in has already been submitted.');

        $validated = $request->validate([
            'monthly_appointments' => 'nullable|in:0-50,51-150,151-300,300+',
            'no_shows_per_month' => 'nullable|in:0,1-2,3-5,6-10,10+',
            'avg_appointment_value' => 'nullable|in:0-100,101-250,251-500,501-1000,1000+',
            'hours_booking_admin' => 'nullable|in:<1,1-3,3-5,5-10,10+',
            'hours_availability_messages' => 'nullable|in:<1,1-3,3-5,5-10,10+',
            'hours_manual_reminders' => 'nullable|in:<1,1-3,3-5,5-10,10+',
            'value_rating' => 'required|integer|min:1|max:5',
            'continuation_likelihood' => 'required|in:very_likely,likely,unsure,unlikely,very_unlikely',
            'continuation_driver' => 'nullable|string|max:2000',
            'churn_driver' => 'nullable|string|max:2000',
            'biggest_change' => 'nullable|string|max:2000',
            'would_recommend' => 'nullable|boolean',
        ]);

        $checkin->update([
            ...$validated,
            'would_recommend' => $request->boolean('would_recommend'),
            'completed_at' => now(),
        ]);

        return redirect()->route('founding-twenty.checkin.show', [$checkin, $token])
            ->with('success', 'Thanks for the update — really appreciate you taking the time.');
    }
}
