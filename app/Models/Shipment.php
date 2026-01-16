<?php

/**
 * Author: Chai Hao Lun
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = ['order_id','shipping_address','shipped_date','delivery_date'];
    protected $casts = ['shipped_date'=>'datetime','delivery_date'=>'datetime'];
    public function order(){ return $this->belongsTo(Order::class); }
}
