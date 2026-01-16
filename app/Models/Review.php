<?php
//Author: Leong Hui Hui
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'book_id',
        'user_id',
        'rating',
        'content',
        'flagged',           // global flag (true/false)
        'hidden',            // manager hid (true/false)
        'manager_trusted'    // optional marker if restored after edit
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}