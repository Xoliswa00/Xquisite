<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Shared endpoint for both notifiable types that can hold a push subscription
 * (staff User via the `web` guard, Customer via the `customer` guard) — each
 * route this is mounted under already sits behind the matching auth
 * middleware, so whichever guard is authenticated on the incoming request is
 * the one the subscription belongs to.
 */
class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint'         => 'required|string',
            'keys.p256dh'      => 'required|string',
            'keys.auth'        => 'required|string',
            'content_encoding' => 'nullable|string',
        ]);

        $notifiable = $this->notifiable();

        $notifiable->updatePushSubscription(
            $data['endpoint'],
            $data['keys']['p256dh'],
            $data['keys']['auth'],
            $data['content_encoding'] ?? null,
        );

        return response()->json(['subscribed' => true]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate(['endpoint' => 'required|string']);

        $this->notifiable()->deletePushSubscription($data['endpoint']);

        return response()->json(['unsubscribed' => true]);
    }

    private function notifiable()
    {
        $notifiable = Auth::guard('web')->user() ?? Auth::guard('customer')->user();

        abort_unless($notifiable, 401);

        return $notifiable;
    }
}
