<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'transaction_id',
        'status',
        'amount',
        'gateway',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
