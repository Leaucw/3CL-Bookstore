<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Http\Controllers;

use App\Models\Event;
use App\Jobs\ScheduleEventJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::orderByDesc('starts_at')->paginate(10);
        return view('staff.events.index', compact('events'));
    }

    public function create()
    {
        return view('staff.events.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'type'          => 'required|string',
            'delivery_mode' => 'required|string',
            'starts_at'     => 'required|date',
            'ends_at'       => 'nullable|date|after_or_equal:starts_at',
            'visibility'    => 'required|string',
            'status'        => 'required|in:draft,scheduled,sent,failed',
            'points_reward' => 'nullable|integer|min:0',
            'image'         => 'nullable|file|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        try {
            $data['slug'] = Str::slug($data['title']) . '-' . uniqid();
            $data['organizer_id'] = auth()->id();
            $data['status'] = 'draft';

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('events', 'public');
                $data['image_path'] = $path;
            }

            $event = Event::create($data);
            ScheduleEventJob::dispatch($event->id);

            // 🔹 Audit log
            Log::info("Audit: Event created", [
                'event_id' => $event->id,
                'user_id'  => auth()->id(),
                'title'    => $event->title,
            ]);

            return redirect()->route('staff.events.index')->with('ok', '✅ Event created and scheduled.');
        } catch (\Throwable $e) {
            Log::error("Event creation failed", [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);
            return back()->with('error', 'Something went wrong while creating the event.');
        }
    }

    public function edit(Event $event)
    {
        return view('staff.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'type'          => 'required|string',
            'delivery_mode' => 'required|string',
            'starts_at'     => 'required|date',
            'ends_at'       => 'nullable|date|after_or_equal:starts_at',
            'visibility'    => 'required|string',
            'status'        => 'required|in:draft,scheduled,sent,failed',
            'points_reward' => 'nullable|integer|min:0',
            'image'         => 'nullable|file|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        try {
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('events', 'public');
                $data['image_path'] = $path;
            }

            $event->update($data);

            // 🔹 Audit log
            Log::info("Audit: Event updated", [
                'event_id' => $event->id,
                'user_id'  => auth()->id(),
            ]);

            return redirect()->route('staff.events.index')->with('ok', '✅ Event updated successfully.');
        } catch (\Throwable $e) {
            Log::error("Event update failed", [
                'event_id' => $event->id,
                'user_id'  => auth()->id(),
                'error'    => $e->getMessage(),
            ]);
            return back()->with('error', 'Something went wrong while updating the event.');
        }
    }

    public function destroy(Event $event)
    {
        try {
            $event->delete();

            // 🔹 Audit log
            Log::info("Audit: Event deleted", [
                'event_id' => $event->id,
                'user_id'  => auth()->id(),
            ]);

            return redirect()->route('staff.events.index')->with('ok', '🗑️ Event deleted.');
        } catch (\Throwable $e) {
            Log::error("Event deletion failed", [
                'event_id' => $event->id,
                'user_id'  => auth()->id(),
                'error'    => $e->getMessage(),
            ]);
            return back()->with('error', 'Unable to delete the event at this time.');
        }
    }
}
