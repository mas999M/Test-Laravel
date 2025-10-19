<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function cartitems()
    {
        return $this->hasMany(CartItem::class , 'product_id');
    }
    public function orders()
    {
        return $this->belongsToMany(Order::class , 'cart_items');
    }
}
