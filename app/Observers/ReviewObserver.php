<?php
//Author: Leong Hui Hui
namespace App\Observers;

use App\Models\Review;
use App\Models\Book;
use Illuminate\Support\Facades\Log;

class ReviewObserver
{
    /**
     * Handle events after a review is created.
     */
    public function created(Review $review): void
    {
        $this->updateAverageRating($review);
        Log::info("Review ID {$review->id} created for Book ID {$review->book_id}");
    }

    /**
     * Handle events after a review is updated.
     */
    public function updated(Review $review): void
    {
        $this->updateAverageRating($review);
        Log::info("Review ID {$review->id} updated for Book ID {$review->book_id}");
    }

    /**
     * Handle events after a review is deleted.
     */
    public function deleted(Review $review): void
    {
        $this->updateAverageRating($review);
        Log::info("Review ID {$review->id} deleted for Book ID {$review->book_id}");
    }

    /**
     * Update average rating for the related book.
     */
    protected function updateAverageRating(Review $review): void
    {
        $book = $review->book;

        if ($book) {
            $average = $book->reviews()->avg('rating');
            $book->average_rating = round($average, 2);
            $book->save();
        }
    }

    /**
     * Optional: handle flagged/unflagged changes
     */
    public function saving(Review $review): void
    {
        if ($review->isDirty('flagged')) {
            $status = $review->flagged ? 'flagged' : 'unflagged';
            Log::info("Review ID {$review->id} has been {$status}");
        }
    }
}
?>