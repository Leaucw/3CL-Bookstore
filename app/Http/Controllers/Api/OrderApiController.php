<?php


/**
 * Author: Chai Hao Lun
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\{Order, OrderItem, CartItem, Shipment, Book, TransactionHistory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class OrderApiController extends Controller
{


    /* ----------------------------
     * List orders (index)
     * ---------------------------- */
   public function index(Request $request)
{
    $orders = Order::where('user_id', $request->user()->id)
        ->with(['items.book', 'shipment', 'transactions']) 
        ->orderByDesc('order_date')
        ->paginate(10);

    $data = $orders->getCollection()->map(function ($order) {
        return [
            'id'           => $order->id,
            'status'       => $order->status,
            'order_date'   => $order->order_date,
            'subtotal'     => $order->subtotal_amount,
            'total'        => $order->total_amount,
            'user_id'      => $order->user_id, 
            'items'        => $order->items->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'book_id'    => $item->book_id, 
                ];
            }),
            'shipment'     => $order->shipment,
            'transactions' => $order->transactions,
        ];
    });

    return response()->json([
        'data' => $data,
        'meta' => [
            'current_page' => $orders->currentPage(),
            'last_page'    => $orders->lastPage(),
            'per_page'     => $orders->perPage(),
            'total'        => $orders->total(),
        ],
    ]);
}


    /* ----------------------------
     * Show order detail
     * ---------------------------- */
    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }


        $order->load('items', 'shipment', 'transactions');

        return response()->json([
            'data' => [
                'id'           => $order->id,
                'status'       => $order->status,
                'order_date'   => $order->order_date,
                'subtotal'     => $order->subtotal_amount,
                'total'        => $order->total_amount,
                'user_id'      => $order->user_id,   
                'items'        => $order->items->map(function ($item) {
                    return [
                        'id'         => $item->id,
                        'quantity'   => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'book_id'    => $item->book_id, 
                    ];
                }),
                'shipment'     => $order->shipment,
                'transactions'=> $order->transactions,
            ]
        ]);
    }





// not used now (for testing purpose only) >>>

    // Direct create by payload {shipping_address, items:[{book_id, quantity}]}
   public function store(StoreOrderRequest $request)
{
    $user    = $request->user();
    $payload = $request->validated();

    $order = DB::transaction(function () use ($user, $payload) {
        $subtotal  = 0.0;
        $shipping  = (float) ($payload['shipping_amount'] ?? 0);
        $usePoints = (bool)  ($payload['use_points'] ?? false);

        // 1) Create the order shell (status = processing; you said no "pending")
        $order = Order::create([
            'user_id'          => $user->id,
            'number'           => 'ORD-'.now()->format('Ymd-His').'-'.str()->random(5),
            'status'           => Order::STATUS_PROCESSING ?? 'processing',
            'order_date'       => now(),
            'shipping_address' => $payload['shipping_address'],
            'payment_method'   => $payload['payment_method'] ?? null,
            'notes'            => $payload['order_note'] ?? null,

            // initialize amounts to zero so NOT NULL columns are satisfied
            'subtotal_amount'  => 0,
            'discount_amount'  => 0,
            'shipping_amount'  => 0,
            'total_amount'     => 0,
        ]);

        // 2) Items
        foreach ($payload['items'] as $row) {
            $book = Book::findOrFail($row['book_id']);
            $qty  = (int) $row['qty'];
            $unit = (float) ($book->price ?? 0);

            // Optional: enforce stock
            // if ($book->stock < $qty) { abort(422, "Insufficient stock for {$book->title}"); }

            OrderItem::create([
                'order_id'   => $order->id,
                'book_id'    => $book->id,
                'quantity'   => $qty,      // use 'quantity' to match your schema
                'unit_price' => $unit,
            ]);

            $subtotal += $qty * $unit;

            // Optional: $book->decrement('stock', $qty);
        }

        // 3) Points redemption (optional)
        $discount = 0.0;
        if ($usePoints) {
            $user->refresh(); // make sure we have fresh points
            $availablePts  = (int) ($user->points ?? 0);
            $maxPts        = (int) round(($subtotal + $shipping) * 100); // 100 pts = RM1
            $pointsUsed    = min($availablePts, $maxPts);
            $discount      = $pointsUsed / 100.0;

            if ($pointsUsed > 0) {
                $user->decrement('points', $pointsUsed);
            }
        }

        // 4) Finalize amounts
        $total = max(0, $subtotal + $shipping - $discount);

        $order->update([
            'subtotal_amount' => $subtotal,
            'discount_amount' => $discount,
            'shipping_amount' => $shipping,
            'total_amount'    => $total,
        ]);

        Shipment::create([
            'order_id'         => $order->id,
            'shipping_address' => $payload['shipping_address'],
            // include these only if columns exist in your shipments table:
            'shipping_method'  => $payload['shipping_method'] ?? 'standard',
            'shipping_amount'  => $shipping,
        ]);

        TransactionHistory::create([
            'order_id'          => $order->id,
            'transaction_date'  => now(),
            'amount'            => $order->total_amount, // positive
            'transaction_type'  => 'Payment',
        ]);

        return $order;
    });

    return (new OrderResource($order->load('items.book')))
        ->response()
        ->setStatusCode(201);
}

    // Update shipping address — allowed only when status = processing
    public function update(UpdateOrderRequest $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $processing = Order::STATUS_PROCESSING ?? 'processing';
        if ($order->status !== $processing) {
            return response()->json([
                'message' => 'You can only change the address while the order is Processing.'
            ], 422);
        }

        $data = $request->validated();
        if (array_key_exists('shipping_address', $data)) {
            $order->update(['shipping_address' => $data['shipping_address']]);
            if ($order->shipment) {
                $order->shipment->update(['shipping_address' => $data['shipping_address']]);
            } else {
                Shipment::create([
                    'order_id'         => $order->id,
                    'shipping_address' => $data['shipping_address']
                ]);
            }
        }

        return new OrderResource($order->fresh('items.book','shipment'));
    }

    // Cancel — allowed only when status = processing
    public function cancel(Request $request, Order $order)
{
    if ($order->user_id !== $request->user()->id) {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    $processing = Order::STATUS_PROCESSING ?? 'processing';
    if ($order->status !== $processing) {
        return response()->json(['message' => 'Only processing orders can be canceled.'], 422);
    }

    DB::transaction(function () use ($order) {
        // mark order as cancelled instead of deleting
        $order->update(['status' => 'cancelled']);

        // record refund transaction
        TransactionHistory::create([
            'order_id'         => $order->id,
            'transaction_date' => now(),
            'amount'           => -1 * (float) ($order->total_amount ?? 0), // negative refund
            'transaction_type' => 'refund',
        ]);

        // optional: restock items if you track stock
        // foreach ($order->items as $item) {
        //     $item->book?->increment('stock', $item->quantity);
        // }
    });

    return response()->json(['message' => 'Order cancelled.'], 200);
}


    // Checkout: create order from current cart
    public function checkout(Request $req)
    {
        $req->validate([
            'payment_method'   => ['required','string','max:100'],
            'shipping_address' => ['required','string','max:2000'],
            'shipping_method'  => ['nullable','in:standard,express'],
            'shipping_amount'  => ['nullable','numeric','min:0'],
            'order_note'       => ['nullable','string','max:2000'],
            'use_points'       => ['sometimes','boolean'],
        ]);

        $userId = $req->user()->id;
        $items  = CartItem::with('book')->where('user_id', $userId)->get();
        if ($items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        $shipping = (float) $req->input('shipping_amount', 0);

        $order = DB::transaction(function () use ($userId, $items, $req, $shipping) {
            $user = \App\Models\User::lockForUpdate()->findOrFail($userId);

            $subtotal = $items->sum(fn($i) => $i->quantity * ($i->book->price ?? 0));
            $preTotal = $subtotal + $shipping;

            // points: 100 pts = RM1
            $availablePoints   = (int) ($user->points ?? 0);
            $maxRedeemablePts  = (int) round($preTotal * 100);
            $willUsePoints     = (bool) $req->boolean('use_points');
            $pointsUsed        = $willUsePoints ? min($availablePoints, $maxRedeemablePts) : 0;
            $discount          = $pointsUsed / 100.0;
            $total             = max(0, $preTotal - $discount);

            $order = Order::create([
                'user_id'         => $userId,
                'order_date'      => now(),
                'status'          => Order::STATUS_PROCESSING ?? 'processing',
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'shipping_amount' => $shipping,
                'total_amount'    => $total,
                'payment_method'  => $req->payment_method,
                'notes'           => $req->input('order_note'),
                'shipping_address'=> $req->shipping_address,
            ]);

            foreach ($items as $ci) {
                $book = Book::lockForUpdate()->find($ci->book_id);
                if (!$book) abort(400, "Book #{$ci->book_id} not found");
                if ($book->stock < $ci->quantity) abort(400, "Not enough stock for {$book->title}");

                OrderItem::create([
                    'order_id'   => $order->id,
                    'book_id'    => $book->id,
                    'quantity'        => $ci->quantity,
                    'unit_price' => $book->price,
                    'subtotal'   => (float) $ci->quantity * (float) ($book->price ?? 0),
                ]);

                $book->decrement('stock', $ci->quantity);
            }

            Shipment::create([
                'order_id'         => $order->id,
                'shipping_address' => $req->shipping_address,
            ]);

            if ($pointsUsed > 0) {
                $user->decrement('points', $pointsUsed);
            }

            TransactionHistory::create([
                'order_id'          => $order->id,
                'transaction_date'  => now(),
                'amount'            => $order->total_amount,
                'transaction_type'  => 'Payment',
            ]);

            CartItem::where('user_id', $userId)->delete();

            return $order;
        });

        return (new OrderResource($order->load('items.book','shipment')))
            ->response()
            ->setStatusCode(201);
    }

    public function staffIndex(Request $request)
{
    // Optional filters: ?status=processing&number=ORD-2025&date_from=2025-09-01&date_to=2025-09-30
    $status    = $request->query('status');       // e.g., processing, shipped, cancelled, completed
    $number    = $request->query('number');       // partial match
    $dateFrom  = $request->query('date_from');    // YYYY-MM-DD
    $dateTo    = $request->query('date_to');      // YYYY-MM-DD
    $perPage   = min((int) $request->query('per_page', 20), 100);

    $q = \App\Models\Order::query()
        ->with(['items.book','shipment','user'])   // include user so staff can see who placed it
        ->orderByDesc('order_date');

    if ($status) {
        $q->where('status', $status);
    }
    if ($number) {
        $q->where('number', 'like', "%{$number}%");
    }
    if ($dateFrom) {
        $q->whereDate('order_date', '>=', $dateFrom);
    }
    if ($dateTo) {
        $q->whereDate('order_date', '<=', $dateTo);
    }

    $orders = $q->paginate($perPage);

    // Return with user info when loaded; OrderResource will handle it if we add a tiny tweak (below)
    return \App\Http\Resources\OrderResource::collection($orders);
}

public function staffShow(Request $request, \App\Models\Order $order)
{
    // Staff/manager can see any order
    $order->load(['items.book','shipment','user','transactions']);
    return new \App\Http\Resources\OrderResource($order);
}

}
