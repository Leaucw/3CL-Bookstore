<?php
//Author: Leau Chee Way
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'genre',
        'description',
        'price',
        'stock',
        'metadata',
        'cover_image_path',
        'average_rating' // keep for observer updates
    ];

    protected $casts = [
        'metadata' => 'array',
        'price' => 'decimal:2',
        'average_rating' => 'decimal:2'
    ];

    // Relationships
    public function categories() { return $this->belongsToMany(Category::class); }
    public function tags()       { return $this->belongsToMany(Tag::class); }
    public function reviews()    { return $this->hasMany(Review::class); }
    public function stockMovements() { return $this->hasMany(StockMovement::class); }

    // Accessors
    protected function coverImageUrl(): Attribute {
        return Attribute::get(fn() => $this->cover_image_path ? Storage::url($this->cover_image_path) : null);
    }

    protected function avgRating(): Attribute {
        return Attribute::get(fn() => round($this->reviews()->avg('rating') ?? 0, 2));
    }

    // Sanitize description input (from latest)
    protected function description(): Attribute
    {
        return Attribute::make(
            set: fn($value) => Purifier::clean($value, 'default')
        );
    }
}