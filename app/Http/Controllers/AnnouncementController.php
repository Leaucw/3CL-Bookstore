<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Jobs\AnnouncementPublishJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::orderByDesc('created_at')->paginate(10);
        return view('staff.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('staff.announcements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'body'     => 'required|string',
            'status'   => 'required|in:draft,scheduled,sent,failed',
            'channels' => 'nullable|array',
        ]);

        try {
            $announcement = Announcement::create($data);

            // 🔹 Audit log
            Log::info("Audit: Announcement created", [
                'announcement_id' => $announcement->id,
                'user_id'         => auth()->id(),
            ]);

            AnnouncementPublishJob::dispatch($announcement->id, [
                'title'      => $announcement->title,
                'message'    => $announcement->body,
                'channels'   => $data['channels'] ?? ['mail'],
                'recipients' => [],
                'meta'       => ['announcement_id' => $announcement->id],
            ]);

            return redirect()->route('staff.ann.index')->with('ok', '📢 Announcement created & queued for publishing.');
        } catch (\Throwable $e) {
            Log::error("Announcement creation failed", [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);
            return back()->with('error', 'Something went wrong while creating the announcement.');
        }
    }

    public function edit(Announcement $announcement)
    {
        return view('staff.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title'  => 'required|string|max:255',
            'body'   => 'required|string',
            'status' => 'required|in:draft,scheduled,sent,failed',
        ]);

        try {
            $announcement->update($data);

            // 🔹 Audit log
            Log::info("Audit: Announcement updated", [
                'announcement_id' => $announcement->id,
                'user_id'         => auth()->id(),
            ]);

            return redirect()->route('staff.ann.index')->with('ok', '📢 Announcement updated.');
        } catch (\Throwable $e) {
            Log::error("Announcement update failed", [
                'announcement_id' => $announcement->id,
                'user_id'         => auth()->id(),
                'error'           => $e->getMessage(),
            ]);
            return back()->with('error', 'Something went wrong while updating the announcement.');
        }
    }

    public function destroy(Announcement $announcement)
    {
        try {
            $announcement->delete();

            // 🔹 Audit log
            Log::info("Audit: Announcement deleted", [
                'announcement_id' => $announcement->id,
                'user_id'         => auth()->id(),
            ]);

            return redirect()->route('staff.ann.index')->with('ok', '🗑️ Announcement deleted.');
        } catch (\Throwable $e) {
            Log::error("Announcement deletion failed", [
                'announcement_id' => $announcement->id,
                'user_id'         => auth()->id(),
                'error'           => $e->getMessage(),
            ]);
            return back()->with('error', 'Unable to delete this announcement.');
        }
    }
}
