<?php


/**
 * Author: Chai Hao Lun
 */

namespace App\Http\Controllers;

use App\Http\Requests\CartAddRequest;
use App\Models\{CartItem};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
    /** Fetch a single book from the web service and normalize keys */
    private function fetchBook(int $id): ?object
    {
        $base = config('services.books_api.base');
        $timeout = (float) config('services.books_api.timeout', 5);

        // cache briefly to avoid spamming your API when rendering the cart
        return Cache::remember("book:$id", 60, function () use ($base, $timeout, $id) {
            $res = Http::acceptJson()->timeout($timeout)->get("$base/books/$id");

            if (!$res->ok()) return null;

            // Adjust this mapping if your API shape differs
            $payload = $res->json();

            // Common patterns: either { data: {...} } or the object directly
            $b = $payload['data'] ?? $payload;

            // Normalize to what your Blade expects: title, author, price, stock, cover_image_url, id
            return (object) [
                'id'               => $b['id']    ?? $id,
                'title'            => $b['title'] ?? 'Untitled',
                'author'           => $b['author'] ?? null,
                'price'            => (float) ($b['price'] ?? 0),
                'stock'            => (int)   ($b['stock'] ?? 0),
                'cover_image_url'  => $b['cover_image_url'] ?? null,
            ];
        });
    }

    public function index()
    {
        // NOTE: no eager-load relation now
        $items = CartItem::where('user_id', auth()->id())->get();

        // Hydrate each item with API-backed "book" object so the Blade can stay unchanged
        $items = $items->map(function ($it) {
            $it->book = $this->fetchBook((int) $it->book_id);  // attach dynamic book
            return $it;
        });

        $subtotal = $items->sum(function ($i) {
            $price = (float) ($i->book->price ?? 0);
            return ((int) $i->quantity) * $price;
        });

        return view('cart.index', compact('items', 'subtotal'));
    }

    public function add(CartAddRequest $request)
    {
        $data = $request->validated();
        $book = $this->fetchBook((int) $data['book_id']);
        if (!$book) {
            return back()->with('err', 'Book not found from web service.');
        }

        if ($book->stock <= 0) {
            return back()->with('err', 'This book is currently out of stock.');
        }

        $item = CartItem::firstOrNew([
            'user_id' => auth()->id(),
            'book_id' => (int) $book->id,
        ]);

        $newQty = ($item->exists ? $item->quantity : 0) + (int) $data['quantity'];

        if ($newQty > $book->stock) {
            return back()->with('err', 'Not enough stock available.');
        }

        $item->quantity = $newQty;
        $item->added_at = now();
        $item->save();

        return back()->with('ok', 'Added to cart!');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== auth()->id()) abort(403);

        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        // validate against API stock
        $book = $this->fetchBook((int) $cartItem->book_id);
        $stock = $book?->stock ?? PHP_INT_MAX;

        if ((int) $data['quantity'] > $stock) {
            return back()->with('err', 'Not enough stock available.');
        }

        $cartItem->update(['quantity' => (int) $data['quantity']]);

        return back()->with('ok', 'Quantity updated.');
    }

    public function remove(CartItem $cartItem)
    {
        if ($cartItem->user_id !== auth()->id()) abort(403);

        $cartItem->delete();

        return back()->with('ok', 'Item removed.');
    }
}
