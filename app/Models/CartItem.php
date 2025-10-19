<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    // ✨ اضافه کن:
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function carts()
    {
        return $this->belongsTo(Cart::class);
    }
}
