<?php

/**
 * Author: Chai Hao Lun
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    protected $fillable = ['order_id','transaction_date','amount','transaction_type'];
    protected $casts = ['transaction_date' => 'datetime','amount' => 'decimal:2'];
    public function order(){ return $this->belongsTo(Order::class); }
}


