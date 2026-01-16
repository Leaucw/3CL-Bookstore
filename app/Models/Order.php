<?php


/**
 * Author: Chai Hao Lun
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\States\Order\OrderStateFactory;

class Order extends Model
{
    use HasFactory;

    // app/Models/Order.php
    protected $fillable = [
        'user_id','order_date','status',
        'subtotal_amount','discount_amount','shipping_amount','total_amount',
        'payment_method','notes',
    ];


    protected $casts = [
        'order_date' => 'datetime',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public const STATUS_PROCESSING = 'Processing';
    public const STATUS_SHIPPED    = 'Shipped';
    public const STATUS_ARRIVED    = 'Arrived';
    public const STATUS_COMPLETED  = 'Completed';
    public const STATUS_CANCELLED  = 'Cancelled';

    public function user(){ return $this->belongsTo(User::class); }
    public function items(){ return $this->hasMany(OrderItem::class); }
    public function transactions(){ return $this->hasMany(TransactionHistory::class); }
    public function shipment(){ return $this->hasOne(Shipment::class); }

    /** Return the state object for current status */
    public function state()
    {
        return OrderStateFactory::for($this);
    }
}
