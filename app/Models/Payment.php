<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'midtrans_order_id',
        'bill',
        'transaction_status',
        'payment_type',
        'transaction_id',
        'fraud_status',
        'payload',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
