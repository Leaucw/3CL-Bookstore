<?php

/**
 * Author: Chai Hao Lun
 */

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\TransactionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
    /**
     * Fetch a book from the external Book API
     */
    private function fetchBook(int $id): ?array
    {
        $base = rtrim(config('services.books_api.base'), '/');
        $res  = Http::acceptJson()->get("$base/books/$id");

        return $res->ok() ? ($res->json()['data'] ?? $res->json()) : null;
    }

    /**
     * List transactions with filters
     */
    public function index(Request $req)
    {
        $q     = trim((string)$req->input('q'));
        $type  = $req->input('type'); // Payment | Refund
        $from  = $req->input('from'); // YYYY-MM-DD
        $to    = $req->input('to');   // YYYY-MM-DD

        $tx = TransactionHistory::with(['order.user','order.items','order.shipment'])
            ->when($type, fn($qq) => $qq->where('transaction_type', $type))
            ->when($from, fn($qq) => $qq->whereDate('transaction_date', '>=', $from))
            ->when($to,   fn($qq) => $qq->whereDate('transaction_date', '<=', $to))
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('id', $q)
                      ->orWhere('order_id', $q)
                      ->orWhereHas('order.user', function ($u) use ($q) {
                          $u->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                      });
                });
            })
            ->orderByDesc('transaction_date')
            ->paginate(20)
            ->withQueryString();

        // Hydrate books via Book API for each transaction’s order items
        $tx->getCollection()->transform(function ($t) {
            if ($t->order) {
                $t->order->items->transform(function ($item) {
                    $item->book = $this->fetchBook($item->book_id);
                    return $item;
                });
            }
            return $t;
        });

        // Simple top-line stats
        $stats = [
            'count'    => TransactionHistory::count(),
            'sum'      => (float) TransactionHistory::sum('amount'),
            'payments' => (float) TransactionHistory::where('transaction_type','Payment')->sum('amount'),
            'refunds'  => (float) TransactionHistory::where('transaction_type','Refund')->sum('amount'),
        ];

        return view('manager.transactions.index', compact('tx','stats'));
    }

    /**
     * Show single transaction detail
     */
    public function show(TransactionHistory $tx)
    {
        $tx->load(['order.user','order.items','order.shipment','order.transactions']);

        // Hydrate books via Book API
        if ($tx->order) {
            $tx->order->items->transform(function ($item) {
                $item->book = $this->fetchBook($item->book_id);
                return $item;
            });
        }

        return view('manager.transactions.show', compact('tx'));
    }
}
