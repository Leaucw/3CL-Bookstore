<?php
//Author: Leau Chee Way
namespace App\Http\Controllers;

use App\Http\Requests\AdjustStockRequest;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class InventoryController extends Controller
{
    /**
     * Web action: adjust stock via form submission
     */
    public function adjust(AdjustStockRequest $req, Book $book): RedirectResponse
    {
        $qty = (int) $req->quantity;
        if ($req->type === 'restock' && $qty < 0) $qty = abs($qty);
        if ($req->type === 'sale' && $qty > 0)  $qty = -abs($qty);

        $lockKey = "adjusting:book:{$book->id}:user:" . auth()->id();
        if (Cache::has($lockKey)) {
            abort(429, 'Too many stock adjustments. Please wait a moment.');
        }
        Cache::put($lockKey, true, 2); 

        DB::transaction(function () use ($book, $req, $qty) {
            $new = $book->stock + $qty;
            if ($new < 0) {
                Log::warning("Blocked stock adjustment attempt", [
                    'book_id' => $book->id,
                    'user_id' => auth()->id(),
                    'qty'     => $qty,
                ]);
                abort(422, 'Insufficient stock');
            }

            $book->update(['stock' => $new]);

            $book->stockMovements()->create([
                'user_id'         => auth()->id(),
                'type'            => $req->type,
                'quantity_change' => $qty,
                'reason'          => $req->reason,
            ]);
        });

        return back()->with('ok', 'Stock updated');
    }
}
