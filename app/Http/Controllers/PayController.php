<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayController extends Controller
{
    // 🛍️ نمایش صفحه محصولات
    public function index()
    {
        $products = Product::all();
        return view('m', compact('products'));
    }

    // ➕ اضافه کردن محصول به سبد خرید
    public function add($id)
    {
        // 1. پیدا کردن محصول
        $product = Product::findOrFail($id);

        // 2. پیدا کردن یا ساختن کارت برای کاربر فعلی
        $cart = Cart::firstOrCreate(
            ['user_id' => Auth::id()]
        );

        // 3. بررسی اینکه محصول قبلاً داخل کارت هست یا نه
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            // 4. اگه قبلاً بوده فقط تعداد رو زیاد کن
            $cartItem->quantity += 1;
            $cartItem->save();
        } else {
            // 5. اگه نبود، آیتم جدید بساز
            $cartItem = new CartItem();
            $cartItem->cart_id = $cart->id;
            $cartItem->product_id = $product->id;
            $cartItem->quantity = 1;
            $cartItem->save();
        }

        return redirect()->back()->with('success', 'محصول به سبد خرید اضافه شد ✅');
    }

    // 🛒 نمایش سبد خرید
    public function cart()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return view('cart', ['cart' => null, 'cartitems' => []]);
        }

        // اصلاح مهم: باید get() یا relation استفاده کنی
        $cartitems = CartItem::where('cart_id', $cart->id)->get();

        return view('cart', compact('cart', 'cartitems'));
    }

    // 💳 تسویه حساب (پرداخت)
    public function checkout()
    {
        $cart = Cart::where('user_id', Auth::id())->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->back()->with('error', 'سبد خرید شما خالی است 🛒');
        }

        // 1. محاسبه مبلغ کل
        $total = 0;
        foreach ($cart->items as $item) {
            $total += $item->product->price * $item->quantity;
        }

        // 2. ساخت سفارش جدید
        $order = Order::create([
            'user_id' => Auth::id(),
            'total' => $total,
            'status' => 'pending', // بعداً می‌تونه paid بشه
        ]);

        // 3. کپی کردن آیتم‌ها از CartItem به OrderItem
        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
            ]);
        }

        // 4. حذف آیتم‌های سبد خرید و خود سبد
        $cart->items()->delete();
        $cart->delete();

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'سفارش شما با موفقیت ثبت شد ✅');
    }
}
