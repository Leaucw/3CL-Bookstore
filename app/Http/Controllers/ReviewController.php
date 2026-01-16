<?php
//Author: Leong Hui Hui
namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Book;
use App\Services\PurchasesApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreReviewRequest;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:customer,staff,manager'])
             ->except('apiRatingsSummary');
    }

    /**
     * Store a new review
     */
    public function store(StoreReviewRequest $request, Book $book)
    {
        $userId = (int) auth()->id();
        $okToReview = null;

        try {
            $client = app(PurchasesApi::class);
            $okToReview = $client->hasPurchased($userId, $book->id);
        } catch (\Throwable $e) {
            $okToReview = null;
        }

        if ($okToReview === false) {
            return back()->with('err', 'You can only review books you have purchased.');
        }

        Review::create([
            'book_id' => $book->id,
            'user_id' => $userId,
            'rating'  => $request->validated()['rating'],
            'content' => $request->validated()['content'] ?? null,
        ]);

        return back()->with('ok', 'Thanks for your review!');
    }

    /**
     * Update review (only by reviewer)
     */
    public function update(Request $request, Book $book, Review $review)
    {
        if ($review->book_id !== $book->id) abort(404);

        $this->authorize('update', $review);

        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'content' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating'  => $request->rating,
            'content' => $request->content,
        ]);

        return redirect()->route('customer.show', $book->id)
                         ->with('success', 'Review updated successfully.');
    }

    /**
     * Delete review (by reviewer or admin)
     */
    public function destroy(Request $request, Book $book, Review $review)
    {
        if ($review->book_id !== $book->id) abort(404);

        $this->authorize('delete', $review);
        $review->delete();

        return redirect()->route('customer.show', $book->id)
                         ->with('success', 'Review deleted successfully.');
    }

    /**
     * Flag review (by other users)
     */
    public function flag(Review $review, Request $request)
{
    if ($review->user_id == auth()->id()) {
        return $request->wantsJson()
            ? response()->json(['error' => 'You cannot flag your own review.'], 403)
            : back()->with('err', 'You cannot flag your own review.');
    }

    $review->update(['flagged' => true]);

    return $request->wantsJson()
        ? response()->json(['message' => 'Review flagged successfully'])
        : back()->with('success', 'Review flagged successfully.');
}

    /**
     * Unflag review (by other users)
     */
    public function unflag(Review $review, Request $request)
{
    if ($review->user_id == auth()->id()) {
        return $request->wantsJson()
            ? response()->json(['error' => 'You cannot unflag your own review.'], 403)
            : back()->with('err', 'You cannot unflag your own review.');
    }

    $review->update(['flagged' => false]);

    return $request->wantsJson()
        ? response()->json(['message' => 'Review unflagged successfully'])
        : back()->with('success', 'Review unflagged successfully.');
}

    /**
     * Manager: list all flagged reviews
     */
    public function flagged()
    {
        $reviews = Review::where('flagged', true)->with('book','user')->get();
        return view('manager.reviews.flagged', compact('reviews'));
    }

    /**
     * Manager: hide a review (keeps flagged status)
     */
    public function managerHide(Review $review)
    {
        $review->update([
            'hidden' => true,
            'manager_trusted' => false,
        ]);

        return back()->with('success', 'Review hidden successfully.');
    }

    /**
     * Manager: unhide a review (auto-clears flag + mark trusted)
     */
    public function managerUnhide(Review $review)
    {
        $review->update([
            'hidden' => false,
            'flagged' => false,
            'manager_trusted' => true,
        ]);

        return back()->with('success', 'Review unhidden and flag cleared.');
    }

    /**
     * Manager: delete review permanently
     */
    public function managerDelete(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Review deleted.');
    }

    /**
     * Manager: trust a review (auto-clear)
     */
    public function managerTrust(Review $review)
    {
        $review->update([
            'hidden' => false,
            'flagged' => false,
            'manager_trusted' => true,
        ]);

        return back()->with('success', 'Review marked as trusted and restored.');
    }

    /**
     * Get logged-in user's review history
     */
    public function myReviews()
    {
        $reviews = Review::where('user_id', auth()->id())
                         ->with('book:id,title')
                         ->get();
        return response()->json($reviews);
    }

    /**
     * Get average rating for a book (only visible reviews)
     */
    public function averageRating(Book $book)
    {
        $average = Review::where('book_id', $book->id)
                         ->where('hidden', false)
                         ->avg('rating');

        return response()->json([
            'book_id' => $book->id,
            'average_rating' => round($average, 2)
        ]);
    }

    /**
     * API: ratings summary (from latest)
     */
    public function apiRatingsSummary(Book $book)
    {
        $agg = Review::where('book_id', $book->id)
                     ->selectRaw('COUNT(*) as count, AVG(rating) as avg')
                     ->first();

        $breakdown = Review::where('book_id', $book->id)
            ->select('rating', DB::raw('COUNT(*) as c'))
            ->groupBy('rating')->pluck('c','rating');

        return response()->json([
            'data' => [
                'book_id'   => $book->id,
                'count'     => (int) ($agg->count ?? 0),
                'avg'       => $agg->avg ? round((float)$agg->avg, 2) : 0.0,
                'breakdown' => (object) $breakdown,
            ]
        ]);
    }
	
	// List all reviews for a book (API)
public function apiList(Book $book)
{
    $reviews = Review::where('book_id', $book->id)
                     ->where('hidden', false)
                     ->with('user:id,name')
                     ->get();

    return response()->json($reviews);
}

// Update review (API)
public function apiUpdate(Request $request, Review $review)
{
    $this->authorize('update', $review); // reviewer only

    $validated = $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'content' => 'nullable|string|max:1000',
    ]);

    $review->update($validated);

    return response()->json([
        'message' => 'Review updated successfully',
        'review'  => $review
    ]);
}

// Delete review (API)
public function apiDestroy(Review $review)
{
    $this->authorize('delete', $review); // reviewer only

    $review->delete();

    return response()->json(['message' => 'Review deleted successfully']);
}
}