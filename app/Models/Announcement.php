<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'body',
        'channels',
        'status',
    ];

    protected $casts = [
        'channels' => 'array', // stored as JSON
    ];

    public const DRAFT = 'draft';
    public const SCHEDULED = 'scheduled';
    public const SENT = 'sent';
    public const FAILED = 'failed';

    // Example: if linked to events in future
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
