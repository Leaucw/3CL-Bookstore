<?php
/**
 * Author: Chan Kah Wei
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'type',
        'delivery_mode',
        'starts_at',
        'ends_at',
        'visibility',
        'status',
        'points_reward',
        'image_path',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    // Status constants
    public const DRAFT = 'draft';
    public const SCHEDULED = 'scheduled';
    public const LIVE = 'live';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';

    // Relationships
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }
}
