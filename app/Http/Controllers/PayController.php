<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;

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




    public function checkout()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return view('cart', ['cart' => null, 'cartitems' => []]);
        }

        // محاسبه مبلغ کل
        $total = 0;
        foreach ($cart->items as $item) {
            $total += $item->product->price * $item->quantity;
        }

        // ساخت سفارش
        $order = Order::create([
            'user_id' => Auth::id(),
            'total' => $total,
            'status' => 'pending',
        ]);

        // ساخت رکورد پرداخت
        $payment = \App\Models\Payment::create([
            'order_id' => $order->id,
            'transaction_id' => '0',
            'reference_id' => '0',
            'amount' => (int)$total,
            'status' => 'pending',
            'gateway' => 'zarinpal',
        ]);

        // ثبت آیتم‌های سفارش
        foreach ($cart->items as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
            ]);
        }

        // حذف سبد خرید بعد از ساخت سفارش
        $cart->items()->delete();
        $cart->delete();

        $amount = (int)$total;

        // ذخیره order_id در session برای مرحله‌ی verify
        session()->put('order_id', $order->id);
        session()->put('amount', $amount);

        // شروع فرآیند پرداخت
        return Payment::callbackUrl(route('verify'))->purchase(
            (new Invoice)->amount($amount),
            function ($driver, $transactionId) use ($order, $payment) {
                // زمانی که درگاه transaction_id برمی‌گردونه
                $order->update(['transaction_id' => $transactionId]);
                $payment->update(['transaction_id' => $transactionId]);
            }
        )->pay()->render();
    }

    public function verify()
    {
        $orderId = session()->get('order_id');
        $amount = session()->get('amount');

        if (!$orderId || !$amount) {
            return 'اطلاعات تراکنش یافت نشد.';
        }

        $order = Order::find($orderId);
        $payment = \App\Models\Payment::where('order_id', $orderId)->first();

        try {
            // بررسی و تأیید پرداخت از درگاه
            $receipt = Payment::amount($amount)
                ->transactionId($order->transaction_id)
                ->verify();

            // اگر بدون خطا بود یعنی پرداخت موفق است
            $order->update([
                'status' => 'success',
            ]);

            $payment->update([
                'status' => 'success',
                'reference_id' => $receipt->getReferenceId(),
            ]);

            echo $receipt->getReferenceId();

        } catch (InvalidPaymentException $exception) {
            // در صورت خطا در تأیید پرداخت
            $order->update([
                'status' => 'failed',
            ]);

            $payment->update([
                'status' => 'failed',
            ]);

            echo $exception->getMessage();
        }
    }


}
