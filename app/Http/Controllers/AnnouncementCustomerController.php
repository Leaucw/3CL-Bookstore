<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Support\Facades\Log;

class AnnouncementCustomerController extends Controller
{
    public function index()
    {
        try {
            $announcements = Announcement::latest()->paginate(10);

            // 🔹 Audit log (customer viewed announcements list)
            Log::info("Audit: Customer viewed announcements list", [
                'user_id' => auth()->id(),
            ]);

            return view('customer.announcements.index', compact('announcements'));
        } catch (\Throwable $e) {
            Log::error("Failed to load announcements list", [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);
            return back()->with('error', 'Unable to load announcements at the moment.');
        }
    }

    public function show(Announcement $announcement)
    {
        try {
            // 🔹 Audit log (customer viewed announcement details)
            Log::info("Audit: Customer viewed announcement", [
                'user_id'         => auth()->id(),
                'announcement_id' => $announcement->id,
            ]);

            return view('customer.announcements.show', compact('announcement'));
        } catch (\Throwable $e) {
            Log::error("Failed to load announcement details", [
                'user_id'         => auth()->id(),
                'announcement_id' => $announcement->id ?? null,
                'error'           => $e->getMessage(),
            ]);
            return back()->with('error', 'Unable to load this announcement at the moment.');
        }
    }
}
