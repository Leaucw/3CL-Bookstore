<?php

/**
 * Author: Chai Hao Lun
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartAddRequest;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class CartApiController extends Controller
{
    /** Fetch a single book from Books API and normalize keys. */
    private function fetchBook(int $id): ?object
    {
        $base    = config('services.books_api.base');
        $timeout = (float) config('services.books_api.timeout', 5);

        $res = Http::retry(2, 150)
            ->timeout($timeout)
            ->acceptJson()
            ->get("$base/books/$id");

        if (!$res->ok()) return null;

        $payload = $res->json();
        $b = $payload['data'] ?? $payload;

        return (object) [
            'id'      => (int)   ($b['id'] ?? $id),
            'title'   => (string)($b['title'] ?? 'Untitled'),
            'author'  => $b['author'] ?? null,
            'price'   => (float) ($b['price'] ?? 0),
            'stock'   => (int)   ($b['stock'] ?? 0),
            'cover_image_url' => $b['cover_image_url'] ?? null,
        ];
    }

    public function index(Request $request)
    {
        $items = CartItem::where('user_id', $request->user()->id)->get();

        // Attach API "book" to each item so the Resource can use $item->book
        $items->transform(function ($it) {
            // avoid Eloquent relation: we’re replacing it with API object
            $it->setRelation('book', null);
            $it->book = $this->fetchBook((int) $it->book_id);
            return $it;
        });

        $subtotal = $items->sum(
            fn($i) => ((int)$i->quantity) * (float)($i->book->price ?? 0)
        );

        return response()->json([
            'data' => CartItemResource::collection($items),
            'meta' => ['subtotal' => (float) $subtotal]
        ]);
    }

    public function store(CartAddRequest $request)
    {
        $data   = $request->validated();
        $userId = $request->user()->id;

        $book = $this->fetchBook((int) $data['book_id']);
        if (!$book) {
            return response()->json(['message' => 'Book not found.'], 404);
        }
        if ($book->stock <= 0) {
            return response()->json(['message' => 'This book is currently out of stock.'], 422);
        }

        $item = CartItem::firstOrNew([
            'user_id' => $userId,
            'book_id' => (int) $book->id,
        ]);

        $newQty = ($item->exists ? (int)$item->quantity : 0) + (int) $data['quantity'];

        if ($newQty > $book->stock) {
            return response()->json(['message' => 'Not enough stock available.'], 422);
        }

        $item->quantity = $newQty;
        $item->added_at = now();
        $item->save();

        // hydrate the API book for the response
        $item->setRelation('book', null);
        $item->book = $book;

        return (new CartItemResource($item))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'quantity' => ['required','integer','min:1']
        ]);

        // validate against Books API stock
        $book = $this->fetchBook((int) $cartItem->book_id);
        if (!$book) {
            return response()->json(['message' => 'Book not found.'], 404);
        }
        if ((int)$data['quantity'] > $book->stock) {
            return response()->json(['message' => 'Not enough stock available.'], 422);
        }

        $cartItem->update(['quantity' => (int) $data['quantity']]);

        // attach API book for response
        $cartItem->setRelation('book', null);
        $cartItem->book = $book;

        return new CartItemResource($cartItem);
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $cartItem->delete();
        return response()->json(['message' => 'Item removed.']);
    }
}
