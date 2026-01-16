<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventApiCustomerController extends Controller
{
    // GET /api/v1/events
    public function index()
    {
        return response()->json(
            Event::where('status', '!=', 'draft')
                ->latest()
                ->paginate(10)
        );
    }

    // GET /api/v1/events/{event}
    public function show(Event $event)
    {
        if ($event->status === 'draft') {
            return response()->json(['message' => 'Event not available'], 403);
        }

        return response()->json($event);
    }

    // POST /api/v1/events/{event}/register
    public function register(Request $request, Event $event)
    {
        // Example: attach current user to pivot table
        if ($request->user()) {
            $request->user()->events()->attach($event->id);
            return response()->json(['message' => 'Successfully registered for event']);
        }

        return response()->json(['message' => 'You must be logged in to register'], 401);
    }
}
