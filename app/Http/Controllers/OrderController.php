<?php


/**
 * Author: Chai Hao Lun
 */

namespace App\Http\Controllers;

use App\Models\{Order, OrderItem, CartItem, Shipment, TransactionHistory};
use App\Payments\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use function App\Helpers\cleanLimitedHtml;

class OrderController extends Controller
{
    /* -----------------------------
     |  Small API client helpers
     |------------------------------*/
     

    private function fetchBook(int $id): ?object
    {
        $base    = config('services.books_api.base');
        $timeout = (float) config('services.books_api.timeout', 5);

        return Cache::remember("book:$id", 60, function () use ($base, $timeout, $id) {
            $res = Http::retry(2, 150)->timeout($timeout)->acceptJson()->get("$base/books/$id");
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
        });
    }

    /** Decrement stock in the Books API (adjust endpoint to your API spec) */
    private function decrementBookStock(int $bookId, int $qty): bool
    {
        $base    = config('services.books_api.base');
        $timeout = (float) config('services.books_api.timeout', 5);

        $res = Http::timeout($timeout)
            ->acceptJson()
            ->post("$base/books/$bookId/decrement", ['quantity' => $qty]);

        return $res->ok();
    }

    /** Read user profile (address, points, etc.) from Users API */
     private function fetchUser(int $id): ?object
    {
        $base    = config('services.users_api.base');
        $timeout = (float) config('services.users_api.timeout', 5);

        $res = Http::retry(2, 150)->timeout($timeout)->acceptJson()->get("$base/users/$id");
        if (!$res->ok()) return null;

        $payload = $res->json();
        $u = $payload['data'] ?? $payload;

        return (object) [
            'id'      => (int)($u['id'] ?? $id),
            'name'    => $u['name'] ?? null,
            'address' => $u['address'] ?? '',
            'points'  => (int)($u['points'] ?? 0),
            // add more fields if needed
        ];
    }


private function fetchUserPoints(int $id): ?int
{
    $base    = rtrim(config('services.users_api.base'), '/');
    $timeout = (float) config('services.users_api.timeout', 5);
    $token   = env('USERS_API_TOKEN');

    $req = Http::acceptJson()->timeout($timeout);
    if ($token) $req = $req->withToken($token);

    $url = "$base/users/$id/points";
    $res = $req->get($url);

    if (!$res->ok()) return null;

    $j = $res->json();
    $d = $j['data'] ?? $j;

    return (int)($d['points'] ?? $d['reward_points'] ?? 0);
}




    /** Redeem points via Users API */

    private function redeemUserPoints(int $userId, int $points): bool
    {
        if ($points <= 0) return true;

        $base    = config('services.users_api.base');
        $timeout = (float) config('services.users_api.timeout', 5);

        // Example endpoint:
        // POST /users/{id}/points/redeem { "points": 250 }
        $res = Http::timeout($timeout)
            ->acceptJson()
            ->post("$base/users/$userId/points/redeem", ['points' => $points]);

        return $res->ok();
    }

    /** Attach API "book" objects to items collection */
    private function hydrateItemsWithBooks($items)
    {
        return $items->map(function ($it) {
            $it->book = $this->fetchBook((int) $it->book_id);
            return $it;
        });
    }

    /* -----------------------------
     |  Screens / Actions
     |------------------------------*/

    // Customer: list their orders
    public function index()
    {
        // Do NOT eager-load items.book via Eloquent; we'll hydrate from API
        $orders = Order::with('items')  // no ->with('items.book')
            ->where('user_id', auth()->id())
            ->orderByDesc('order_date')
            ->paginate(200);

        // Optionally hydrate "book" for each order's items
        $orders->getCollection()->transform(function ($order) {
            $order->setRelation('items', $this->hydrateItemsWithBooks($order->items));
            return $order;
        });

        return view('orders.index', compact('orders'));
    }

    public function updateAddress(Request $request, Order $order)
    {
        $this->authorizeAdminOrOwner($order);
        if ($order->status !== Order::STATUS_PROCESSING) {
            return back()->with('err', 'You can only change the address while the order is Processing.');
        }

        $data = $request->validate([
            'shipping_address' => ['required','string','max:2000'],
        ]);

        $order->load('shipment');
        if ($order->shipment) {
            $order->shipment->update(['shipping_address' => $data['shipping_address']]);
        } else {
            Shipment::create([
                'order_id'         => $order->id,
                'shipping_address' => $data['shipping_address'],
            ]);
        }

        return back()->with('ok', 'Shipping address updated.');
    }

    // Customer: show one order
    public function show(Order $order)
    {
        $this->authorizeAdminOrOwner($order);

        // load relations except book; then hydrate book via API
        $order->load('items','shipment','transactions');
        $order->setRelation('items', $this->hydrateItemsWithBooks($order->items));

        return view('orders.show', compact('order'));
    }

    public function showCheckout()
    {
        // Pull cart items (local), but hydrate book via API (NOT Eloquent)
        $items = CartItem::where('user_id', auth()->id())->get();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('err','Your cart is empty.');
        }
        $items = $this->hydrateItemsWithBooks($items);

        // Read user from API (address & points), not DB
        $apiUser = $this->fetchUser((int) auth()->id());
        if (!$apiUser) {
            return redirect()->route('cart.index')->with('err','Unable to load your profile. Please try again.');
        }
        
if (method_exists($this, 'fetchUserPoints')) {
    $p = $this->fetchUserPoints((int)$apiUser->id);
    if ($p !== null) $apiUser->points = $p;
}

$subtotal    = $items->sum(fn($i) => ((int)$i->quantity) * (float)($i->book->price ?? 0));
$userAddress = $apiUser->address ?: (auth()->user()->address ?? '');
$userPoints  = isset($apiUser->points) ? (int)$apiUser->points : (int)(auth()->user()->points ?? 0);

return view('orders.checkout', compact('items','subtotal','userAddress','userPoints'));

    }

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

    $manager  = app(PaymentManager::class);
    $strategy = $manager->resolve($req->payment_method);
    $strategy->validate($req);

    $userId = (int) auth()->id();

    // cart items (local) + hydrate books via API
    $items = CartItem::where('user_id', $userId)->get();
    if ($items->isEmpty()) {
        return redirect()->route('cart.index')->with('err','Your cart is empty.');
    }
    $items = $this->hydrateItemsWithBooks($items);

    // user (via API)
    $apiUser = $this->fetchUser($userId);
    if (!$apiUser) {
        return back()->with('err', 'Unable to load your profile.');
    }

    if (method_exists($this, 'fetchUserPoints')) {
        $p = $this->fetchUserPoints($userId);
        if ($p !== null) $apiUser->points = $p;
    }

    $shipping = (float) $req->input('shipping_amount', 0);

    // Totals
    $subtotal = $items->sum(fn($i) => ((int)$i->quantity) * (float)($i->book->price ?? 0));
    $preTotal = $subtotal + $shipping;

    // Points: 100 pts = RM1
    $availablePoints   = (int) $apiUser->points;
    $maxRedeemablePts  = (int) round($preTotal * 100);
    $willUsePoints     = (bool) $req->boolean('use_points');
    $pointsUsed        = $willUsePoints ? min($availablePoints, $maxRedeemablePts) : 0;
    $discount          = $pointsUsed / 100.0;
    $total             = max(0, $preTotal - $discount);

    $notes = cleanLimitedHtml($req->input('order_note'));

    /* ----------------------------
     * Step 1: Reserve stock via API
     * ---------------------------- */
    $reservations = [];
    foreach ($items as $ci) {
        $book = $ci->book;
        if (!$book) {
            return back()->with('err', "Book #{$ci->book_id} not found");
        }
        if ($book->stock < $ci->quantity) {
            return back()->with('err', "Not enough stock for {$book->title}");
        }

        $ok = $this->decrementBookStock((int)$book->id, (int)$ci->quantity);
        if (!$ok) {
            // rollback any prior reservations
            foreach ($reservations as [$bid, $qty]) {
                $this->incrementBookStock($bid, $qty);
            }
            return back()->with('err', "Failed to reserve stock for {$book->title}");
        }
        $reservations[] = [(int)$book->id, (int)$ci->quantity];
    }

    try {
        /* ----------------------------------------
         * Step 2: Persist order in a short TX
         * ---------------------------------------- */
        $order = DB::transaction(function () use ($userId, $items, $req, $subtotal, $shipping, $discount, $total, $notes) {

            $order = Order::create([
                'user_id'         => $userId,
                'order_date'      => now(),
                'status'          => Order::STATUS_PROCESSING,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'shipping_amount' => $shipping,
                'total_amount'    => $total,
                'payment_method'  => $req->payment_method,
                'notes'           => $notes,
            ]);

            foreach ($items as $ci) {
                $book = $ci->book;
                OrderItem::create([
                    'order_id'   => $order->id,
                    'book_id'    => (int) $book->id,
                    'quantity'   => (int) $ci->quantity,
                    'unit_price' => (float) $book->price,
                ]);
            }

            Shipment::create([
                'order_id'         => $order->id,
                'shipping_address' => $req->shipping_address,
            ]);

            return $order;
        });

        /* ----------------------------------------
         * Step 3: Charge payment
         * ---------------------------------------- */
        $strategy->charge($order, $total, $req->all());

        /* ----------------------------------------
         * Step 4: Redeem points via Users API
         * ---------------------------------------- */
        if ($pointsUsed > 0) {
            $ok = $this->redeemUserPoints($userId, $pointsUsed);
            if (!$ok) {
                // not fatal: you could rollback, or just log
                logger()->warning("Points redemption failed for user $userId, order {$order->id}");
            }
        }

        /* ----------------------------------------
         * Step 5: Clear cart
         * ---------------------------------------- */
        CartItem::where('user_id', $userId)->delete();

        return redirect()->route('orders.show', $order)->with('ok','Payment successful. Order created.');

    } catch (\Throwable $e) {
        // Compensate stock if local order fails
        foreach ($reservations as [$bid, $qty]) {
            $this->incrementBookStock($bid, $qty);
        }
        throw $e;
    }
}


    // ===== State transitions =====

    public function ship(Order $order)
    {
        $this->authorizeAdminOrOwner($order);
        $order->load('items','shipment');
        $order->state()->ship();
        return back()->with('ok', 'Order shipped');
    }

    public function arrive(Order $order)
    {
        $this->authorizeAdminOrOwner($order);
        $order->load('shipment');
        $order->state()->arrive();
        return back()->with('ok', 'Order marked as arrived');
    }

    public function complete(Order $order)
    {
        $this->authorizeAdminOrOwner($order);
        $order->state()->complete();
        return back()->with('ok', 'Order completed');
    }

    public function cancel(Order $order)
    {
        $this->authorizeAdminOrOwner($order);
        $order->load('items');
        $order->state()->cancel();
        return back()->with('ok', 'Order cancelled & refunded');
    }

    private function authorizeAdminOrOwner(Order $order): void
    {
        $user = auth()->user();
        if (! $user) abort(401);
        if (in_array($user->role, ['admin','staff'], true)) return;
        if ($order->user_id === $user->id) return;
        abort(403);
    }
}

