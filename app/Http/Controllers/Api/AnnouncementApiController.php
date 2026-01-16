<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementApiController extends Controller
{
    // GET /api/v1/staff/announcements
    public function index()
    {
        return response()->json(Announcement::latest()->paginate(10));
    }

    // GET /api/v1/staff/announcements/{announcement}
    public function show(Announcement $announcement)
    {
        return response()->json($announcement);
    }

    // POST /api/v1/staff/announcements
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'  => 'required|string|max:255',
            'body'   => 'nullable|string',
            'status' => 'required|in:draft,scheduled,sent,failed'
        ]);

        $announcement = Announcement::create($data);

        return response()->json([
            'message' => 'Announcement created successfully',
            'data'    => $announcement
        ], 201);
    }

    // PUT /api/v1/staff/announcements/{announcement}
    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title'  => 'sometimes|string|max:255',
            'body'   => 'nullable|string',
            'status' => 'sometimes|in:draft,scheduled,sent,failed'
        ]);

        $announcement->update($data);

        return response()->json([
            'message' => 'Announcement updated successfully',
            'data'    => $announcement
        ]);
    }

    // DELETE /api/v1/staff/announcements/{announcement}
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully']);
    }
}
