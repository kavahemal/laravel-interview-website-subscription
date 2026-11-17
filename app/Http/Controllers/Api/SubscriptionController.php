<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriberResource;
use App\Models\Subscriber;
use App\Models\Subscription;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscribers = Subscriber::withCount('subscriptions')
            ->with(['subscriptions.website'])
            ->latest()
            ->get();

        return SubscriberResource::collection($subscribers);
    }

    public function store(Request $request, Website $website)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        $subscriber = Subscriber::firstOrCreate(
            ['email' => Str::lower($validated['email'])],
            ['name' => $validated['name'] ?? null]
        );

        $subscription = Subscription::firstOrCreate([
            'subscriber_id' => $subscriber->id,
            'website_id' => $website->id,
        ]);

        if (! $subscription->wasRecentlyCreated) {
            return response()->json(['message' => 'Already subscribed to this website.'], 409);
        }

        return response()->json(['message' => 'Subscribed successfully'], 201);
    }
}
