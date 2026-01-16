<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventApiController extends Controller
{
    // GET /api/v1/staff/events
    public function index()
    {
        return response()->json(Event::latest()->paginate(10));
    }

    // GET /api/v1/staff/events/{event}
    public function show(Event $event)
    {
        return response()->json($event);
    }

    // POST /api/v1/staff/events
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|string',
            'delivery_mode'=> 'required|in:online,onsite,hybrid',
            'starts_at'    => 'required|date',
            'ends_at'      => 'nullable|date|after_or_equal:starts_at',
            'visibility'   => 'required|in:public,private,targeted',
            'status'       => 'required|in:draft,scheduled,live,completed,cancelled',
            'points_reward'=> 'nullable|integer|min:0',
            'event_image'  => 'nullable|string' // path/url
        ]);

        $event = Event::create($data);

        return response()->json([
            'message' => 'Event created successfully',
            'data'    => $event
        ], 201);
    }

    // PUT /api/v1/staff/events/{event}
    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'sometimes|string',
            'delivery_mode'=> 'sometimes|in:online,onsite,hybrid',
            'starts_at'    => 'sometimes|date',
            'ends_at'      => 'nullable|date|after_or_equal:starts_at',
            'visibility'   => 'sometimes|in:public,private,targeted',
            'status'       => 'sometimes|in:draft,scheduled,live,completed,cancelled',
            'points_reward'=> 'nullable|integer|min:0',
            'event_image'  => 'nullable|string'
        ]);

        $event->update($data);

        return response()->json([
            'message' => 'Event updated successfully',
            'data'    => $event
        ]);
    }

    // DELETE /api/v1/staff/events/{event}
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }
}
