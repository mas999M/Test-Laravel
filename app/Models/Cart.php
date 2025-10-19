<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id'];

    public function user()
    {
         $this->belongsTo(User::class , 'user_id');
    }
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

}
