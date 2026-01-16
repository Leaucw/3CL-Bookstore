<?php
//Author: Leau Chee Way 
namespace App\Http\Controllers;

use App\Http\Requests\StoreUpdateBookRequest;
use App\Models\Book;
use App\Models\Category;
use App\Services\CatalogFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * @method void authorize($ability, $arguments = [])
 */
class BookController extends Controller
{
    public function __construct(private CatalogFacade $catalog) {}

    public function index(Request $r)
    {
        $threshold = (int) $r->input('low', 5);
        $q = trim((string) $r->input('q', ''));

        $books = Book::with(['categories','tags'])
            ->when($r->search, fn($q,$s) => $q->where(fn($w) => $w
                ->where('title','like',"%$s%")
                ->orWhere('author','like',"%$s%")
                ->orWhere('isbn','like',"%$s%")))
            ->latest()
            ->paginate(12);
        
        $stats = [
            'total' => Book::count(),
            'low'   => Book::where('stock', '<',  $threshold)->count(),
            'out'   => Book::where('stock', '<=', 0)->count(),
        ];

        return view('books.index', compact('books','threshold','stats'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get(['id','name']);
        return view('books.create', compact('categories'));
    }

    public function store(StoreUpdateBookRequest $req)
    {
        $book = $this->catalog->createBook(
            $req->validated() + ['cover_image' => $req->file('cover_image')]
        );

        return redirect()->route('books.show', $book)->with('ok','Book created');
    }

    public function show(Book $book)
    {
        // Eager-load categories, tags, reviews with users (keep your module change)
        $book->load([
            'categories',
            'tags',
            'reviews' => fn($q) => $q->latest()->with('user')
        ]);

        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $categories = Category::orderBy('name')->get(['id','name']);
        $book->load('categories');
        return view('books.edit', compact('book','categories'));
    }

    public function update(StoreUpdateBookRequest $req, Book $book)
    {
        $this->catalog->updateBook(
            $book,
            $req->validated() + ['cover_image' => $req->file('cover_image')]
        );

        return redirect()->route('books.show',$book)->with('ok','Book updated');
    }

    public function destroy(Book $book)
    {
        $book->delete(); // soft delete
        return redirect()->route('books.index')->with('ok','Book discontinued');
    }

    public function customerIndex()
    {
        $books = \App\Models\Book::with('categories')
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(12);

        return view('customer.index', compact('books'));
    }

    public function customerShow(Book $book)
    {
        abort_if(!is_null($book->deleted_at), 404);

        // Keep your module change: include user with reviews
        $book->load([
            'categories',
            'reviews' => fn($q) => $q->latest()->with('user')
        ]);

        return view('customer.show', compact('book'));
    }
}