<?php
//Author: Leau Chee Way
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BookApiController extends Controller
{
    /**
     * API: GET /api/v1/books
     */
    public function index(Request $request)
    {
        $q = Book::query()->with('categories');

        if ($search = trim((string) $request->query('q', ''))) {
            $q->where(function ($w) use ($search) {
                $w->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($cat = $request->query('category_id')) {
            $q->whereHas('categories', fn($w) => $w->where('categories.id', (int) $cat));
        }

        $books = $q->latest()->paginate(12);

        return response()->json([
            'data' => $books->getCollection()->map(function (Book $b) {
                return [
                    'id'              => $b->id,
                    'title'           => $b->title,
                    'author'          => $b->author,
                    'isbn'            => $b->isbn,
                    'price'           => $b->price,
                    'stock'           => $b->stock ?? null,
                    'categories'      => $b->categories->pluck('name'),
                    'cover_image_url' => $b->cover_image_path 
                        ? asset('storage/' . $b->cover_image_path) 
                        : null,
                ];
            })->values(),
            'meta' => [
                'current_page' => $books->currentPage(),
                'last_page'    => $books->lastPage(),
                'total'        => $books->total(),
            ],
        ]);
    }

    /**
     * API: GET /api/v1/books/{book}
     */
    public function show(Book $book)
    {
        $book->load([
            'categories:id,name',
            'reviews' => fn($r) => $r->latest()->limit(5),
        ]);

        return response()->json([
            'data' => [
                'id'              => $book->id,
                'title'           => $book->title,
                'author'          => $book->author,
                'isbn'            => $book->isbn,
                'price'           => $book->price,
                'stock'           => $book->stock ?? null,
                'categories'      => $book->categories->pluck('name'),
                'reviews'         => $book->reviews->map(fn($rv) => [
                    'user_id' => $rv->user_id,
                    'rating'  => $rv->rating,
                    'content' => $rv->content,
                    'date'    => optional($rv->created_at)->toDateTimeString(),
                ]),
                'cover_image_url' => $book->cover_image_path 
                    ? asset('storage/' . $book->cover_image_path) 
                    : null,
            ],
        ]);
    }

    /**
     * API: POST /api/v1/books
     */
    public function store(Request $request)
    {
        if (!$this->checkUserRole($request, ['staff'])) {
            return response()->json(['error' => 'Forbidden – only staff/manager can add books'], 403);
        }

        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'author'   => 'required|string|max:255',
            'isbn'     => 'required|string|max:50|unique:books,isbn',
            'price'    => 'required|numeric|min:0',
            'stock'    => 'required|integer|min:0',
            'category_ids' => 'array'
        ]);

        $book = Book::create($data);

        if (!empty($data['category_ids'])) {
            $book->categories()->sync($data['category_ids']);
        }

        return response()->json(['data' => $book], 201);
    }

    /**
     * API: PUT /api/v1/books/{book}
     */
    public function update(Request $request, Book $book)
    {
        if (!$this->checkUserRole($request, ['staff'])) {
            return response()->json(['error' => 'Forbidden – only staff/manager can update books'], 403);
        }

        $data = $request->validate([
            'title'    => 'sometimes|string|max:255',
            'author'   => 'sometimes|string|max:255',
            'isbn'     => "sometimes|string|max:50|unique:books,isbn,{$book->id}",
            'price'    => 'sometimes|numeric|min:0',
            'stock'    => 'sometimes|integer|min:0',
            'category_ids' => 'array'
        ]);

        $book->update($data);

        if (isset($data['category_ids'])) {
            $book->categories()->sync($data['category_ids']);
        }

        return response()->json(['data' => $book]);
    }

    /**
     * API: DELETE /api/v1/books/{book}
     */
    public function destroy(Request $request, Book $book)
    {
        if (!$this->checkUserRole($request, ['staff'])) {
            return response()->json(['error' => 'Forbidden – only staff/manager can delete books'], 403);
        }

        $book->delete();
        return response()->json(['message' => 'Book deleted']);
    }

    /**
     * 🔑 Helper: Verify role using User Management API
     */
    private function checkUserRole(Request $request, array $allowedRoles): bool
    {
        if ($request->query('use_api', false)) {
            try {
                $token = $request->bearerToken();

                $response = Http::timeout(5)
                    ->withToken($token)
                    ->get('http://127.0.0.1:8001/api/v1/auth/me');

                if ($response->failed()) {
                    throw new \Exception('Failed to fetch user from Auth API');
                }

                $user = $response->json();
                $role = $user['role'] ?? ($user['user']['role'] ?? null);

                return in_array($role, $allowedRoles);
            } catch (\Throwable $e) {
                return false;
            }
        }

        $user = $request->user();
        return $user && in_array($user->role ?? '', $allowedRoles);
    }

    /**
 * API: POST /api/v1/books/{book}/decrement
 * Body JSON: { "quantity": 3 }
 */
    public function decrement(Request $request, Book $book)
    {
        $data = $request->validate([
            'quantity' => ['required','integer','min:1'],
        ]);

        $qty = (int) $data['quantity'];

        $lockKey = "api:decrement:book:{$book->id}:ip:" . $request->ip();
        if (Cache::has($lockKey)) {
            return response()->json([
                'ok'      => false,
                'message' => 'Too many requests. Please wait a moment.',
            ], 429);
        }
        Cache::put($lockKey, true, 2);

        try {
            DB::transaction(function () use ($book, $qty) {
                $newStock = $book->stock - $qty;

                if ($newStock < 0) {
                    Log::warning("API stock decrement blocked", [
                        'book_id' => $book->id,
                        'qty'     => $qty,
                    ]);

                    abort(422, 'Insufficient stock');
                }

                $book->update(['stock' => $newStock]);

                if (method_exists($book, 'stockMovements')) {
                    $book->stockMovements()->create([
                        'type'            => 'sale',
                        'quantity_change' => -$qty,
                        'reason'          => 'API decrement',
                    ]);
                }
            });

            $book->refresh();

            return response()->json([
                'ok'   => true,
                'data' => [
                    'book_id' => $book->id,
                    'stock'   => (int) $book->stock,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("API decrement error", [
                'book_id' => $book->id,
                'qty'     => $qty,
                'error'   => $e->getMessage(),
            ]);
            return response()->json([
                'ok'      => false,
                'message' => 'Stock update failed. Please try again later.',
            ], 500);
        }
    }

}
