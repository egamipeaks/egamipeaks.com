<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Contracts\View\View;

class SubscriptionController extends Controller
{
    public function showVerify(string $token): View
    {
        $subscriber = Subscriber::query()
            ->where('verify_token', $token)
            ->firstOrFail();

        return view('subscribers.verify', [
            'token' => $token,
            'alreadyVerified' => $subscriber->isVerified(),
        ]);
    }

    public function verify(string $token): View
    {
        $subscriber = Subscriber::query()
            ->where('verify_token', $token)
            ->firstOrFail();

        $alreadyVerified = $subscriber->isVerified();

        if (! $alreadyVerified) {
            $subscriber->markVerified();
        }

        return view('subscribers.verified', [
            'alreadyVerified' => $alreadyVerified,
        ]);
    }

    public function showUnsubscribe(string $token): View
    {
        $subscriber = Subscriber::query()
            ->where('unsubscribe_token', $token)
            ->firstOrFail();

        return view('subscribers.unsubscribe', [
            'token' => $token,
            'email' => $subscriber->email,
        ]);
    }

    public function unsubscribe(string $token): View
    {
        $subscriber = Subscriber::query()
            ->where('unsubscribe_token', $token)
            ->firstOrFail();

        $email = $subscriber->email;
        $subscriber->delete();

        return view('subscribers.unsubscribed', [
            'email' => $email,
        ]);
    }
}
