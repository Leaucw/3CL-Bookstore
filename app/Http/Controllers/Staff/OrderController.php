<?php

/**
 * Author: Chai Hao Lun
 */

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    /**
     * Fetch a book from the external Book API
     */
    private function fetchBook(int $id): ?object
{
    $base = rtrim(config('services.books_api.base'), '/');
    $res  = Http::acceptJson()->get("$base/books/$id");

    if (!$res->ok()) {
        return null;
    }

    $data = $res->json()['data'] ?? $res->json();

    return (object) [
        'id'              => $data['id'] ?? $id,
        'title'           => $data['title'] ?? null,
        'author'          => $data['author'] ?? null,
        'price'           => $data['price'] ?? null,
        'stock'           => $data['stock'] ?? null,
        'cover_image_url' => $data['cover_image_url'] 
                          ?? $data['cover_image_path'] 
                          ?? null, // flexible mapping
    ];
}

    /**
     * Staff: List all orders with filters
     */
    public function index(Request $req)
    {
        $q      = trim((string) $req->q);
        $status = $req->status;                // Processing|Shipped|Arrived|Completed|Cancelled
        $from   = $req->from;                  // YYYY-MM-DD
        $to     = $req->to;                    // YYYY-MM-DD

        // Do NOT eager-load items.book (we'll hydrate via API instead)
        $orders = Order::with(['user','items','shipment'])
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($from,   fn($qq) => $qq->whereDate('order_date', '>=', $from))
            ->when($to,     fn($qq) => $qq->whereDate('order_date', '<=', $to))
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('id', $q)
                      ->orWhereHas('user', fn($u) => $u->where('name','like',"%{$q}%")
                                                       ->orWhere('email','like',"%{$q}%"));
                });
            })
            ->orderByDesc('order_date')
            ->paginate(200)
            ->withQueryString();

        // Hydrate books via Book API
        $orders->getCollection()->transform(function ($order) {
            $order->items->transform(function ($item) {
                $item->book = $this->fetchBook($item->book_id);
                return $item;
            });
            return $order;
        });

        return view('staff.orders.index', compact('orders'));
    }

    /**
     * Staff: Show single order detail
     */
    public function show(Order $order)
    {
        // Do NOT eager-load items.book, only local relations
        $order->load(['user','items','shipment','transactions']);

        // Hydrate books via Book API
        $order->items->transform(function ($item) {
            $item->book = $this->fetchBook($item->book_id);
            return $item;
        });

        return view('staff.orders.show', compact('order'));
    }
}
