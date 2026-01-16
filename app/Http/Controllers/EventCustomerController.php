<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Jobs\AwardPointsForRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EventCustomerController extends Controller
{
    public function index()
    {
        try {
            $events = Event::whereNotIn('status', ['cancelled'])
                ->orderBy('starts_at')
                ->paginate(10);

            // 🔹 Audit log
            Log::info("Audit: Customer viewed events list", [
                'user_id' => auth()->id(),
            ]);

            return view('customer.events.index', compact('events'));
        } catch (\Throwable $e) {
            Log::error("Failed to load events list", [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);
            return back()->with('error', 'Unable to load events at the moment.');
        }
    }

    public function show(Event $event)
    {
        try {
            // 🔹 Audit log
            Log::info("Audit: Customer viewed event details", [
                'user_id'  => auth()->id(),
                'event_id' => $event->id,
            ]);

            return view('customer.events.show', compact('event'));
        } catch (\Throwable $e) {
            Log::error("Failed to load event details", [
                'user_id'  => auth()->id(),
                'event_id' => $event->id ?? null,
                'error'    => $e->getMessage(),
            ]);
            return back()->with('error', 'Unable to load event details at the moment.');
        }
    }

    public function register(Request $request, Event $event)
    {
        try {
            $user = auth()->user();

            // 🔹 Save registration with user details
            $registration = EventRegistration::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'user_id'  => $user->id,
                ],
                [
                    'name'  => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '', // safe default if null
                ]
            );

            // 🔹 Award points asynchronously
            AwardPointsForRegistration::dispatch($registration->id);

            $userId = $user->id;
            $points = 0;
            $success = false;

            // 🔹 Try EXTERNAL API first
            try {
                $base = rtrim(config('services.users_api.base'), '/');
                $timeout = (float) config('services.users_api.timeout', 5);

                $res = Http::timeout($timeout)
                    ->acceptJson()
                    ->get("$base/users/{$userId}/points");

                if ($res->ok() && isset($res['data']['points'])) {
                    $points = (int) $res['data']['points'];
                    $success = true;
                }
            } catch (\Throwable $e) {
                Log::warning("External API points fetch failed", [
                    'user_id' => $userId,
                    'error'   => $e->getMessage(),
                ]);
            }

            // 🔹 If external fails → INTERNAL API
            if (!$success) {
                try {
                    $internalBase = url('/api/v1');
                    $res = Http::acceptJson()
                        ->get("$internalBase/users/{$userId}/points");

                    if ($res->ok() && isset($res['data']['points'])) {
                        $points = (int) $res['data']['points'];
                    }
                } catch (\Throwable $e) {
                    Log::error("Internal API points fetch failed", [
                        'user_id' => $userId,
                        'error'   => $e->getMessage(),
                    ]);
                    $points = 0; // safe default
                }
            }

            // 🔹 Audit log
            Log::info("Audit: Customer registered for event", [
                'user_id'         => $userId,
                'event_id'        => $event->id,
                'registration_id' => $registration->id,
                'points_after'    => $points,
            ]);

            return redirect()
                ->route('cust.events.show', $event->slug)
                ->with('ok', "🎉 You have successfully registered for this event! Your total points: {$points}");
        } catch (\Throwable $e) {
            Log::error("Event registration failed", [
                'user_id' => auth()->id(),
                'event_id'=> $event->id ?? null,
                'error'   => $e->getMessage(),
            ]);
            return back()->with('error', 'Something went wrong while registering for the event.');
        }
    }
}
