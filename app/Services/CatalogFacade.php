<?php
//Author: Leau Chee Way
namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CatalogFacade
{
    public function createBook(array $data): Book
    {
        return DB::transaction(function () use ($data) {
            $categoryId = $data['category_id'] ?? null;
            $cover      = $data['cover_image'] ?? null;

            unset($data['category_id'], $data['cover_image'], $data['tag_ids']);

            $book = Book::create($data);

            $book->categories()->sync($categoryId ? [$categoryId] : []);

            if (!empty($data['tag_ids'])) {
                $book->tags()->sync($data['tag_ids']);
            }

            if ($cover) {
                $path = $cover->store('books', 'public');
                $book->update(['cover_image_path' => $path]);
            }

            if (($book->stock ?? 0) > 0) {
                $book->stockMovements()->create([
                    'user_id'         => auth()->id(),
                    'type'            => 'restock',
                    'quantity_change' => $book->stock,
                    'reason'          => 'initial load',
                ]);
            }

            return $book;
        });
    }

    public function updateBook(Book $book, array $data): Book
    {
        return DB::transaction(function () use ($book, $data) {
            $categoryId = $data['category_id'] ?? null;
            $cover      = $data['cover_image'] ?? null;

            unset($data['category_id'], $data['cover_image'], $data['tag_ids']);

            $book->update($data);
            $book->categories()->sync($categoryId ? [$categoryId] : []);

            if (!empty($data['tag_ids'])) {
                $book->tags()->sync($data['tag_ids']);
            } else {
                $book->tags()->sync([]);
            }

            if ($cover) {
                if ($book->cover_image_path && Storage::disk('public')->exists($book->cover_image_path)) {
                    Storage::disk('public')->delete($book->cover_image_path);
                }
                $path = $cover->store('books', 'public');
                $book->update(['cover_image_path' => $path]);
            }

            return $book;
        });
    }
}
