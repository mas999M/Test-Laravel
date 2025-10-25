<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->get('/m' , [\App\Http\Controllers\PayController::class , 'index']);
Route::middleware('auth')->get('/add/{id}' , [\App\Http\Controllers\PayController::class , 'add'])->name('add');
Route::middleware('auth')->get('/cart' , [\App\Http\Controllers\PayController::class , 'cart'])->name('cart');
Route::middleware('auth')->get('/checkout', [\App\Http\Controllers\PayController::class, 'checkout'])->name('checkout');
Route::get('verify',[\App\Http\Controllers\PayController::class , 'verify'])->name('verify');

// برای نمایش سفارش
//Route::get('/orders/{order}', function (\App\Models\Order $orders) {
//    return view('order-show', compact('orders'));
//})->name('orders.show');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
