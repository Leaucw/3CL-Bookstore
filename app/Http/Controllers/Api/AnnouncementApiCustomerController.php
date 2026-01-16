<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;

class AnnouncementApiCustomerController extends Controller
{
    // GET /api/v1/announcements
    public function index()
    {
        return response()->json(
            Announcement::where('status', '!=', 'draft')
                ->latest()
                ->paginate(10)
        );
    }

    // GET /api/v1/announcements/{announcement}
    public function show(Announcement $announcement)
    {
        if ($announcement->status === 'draft') {
            return response()->json(['message' => 'Announcement not available'], 403);
        }

        return response()->json($announcement);
    }
}
