<?php
//Author: Leau Chee Way
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'book_id','user_id','type','quantity_change','resulting_stock','reason'
    ];

    public function book() { return $this->belongsTo(Book::class); }
    public function user() { return $this->belongsTo(User::class); }
}
