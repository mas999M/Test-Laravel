<?php
namespace App\Listeners;

use App\Events\OrderPlaced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmation implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\OrderPlaced  $event
     * @return void
     */
    public function handle(OrderPlaced $event)
    {
        // کارهایی که باید انجام بشه، مثل ارسال ایمیل یا ذخیره‌سازی
        // این کد به صورت غیرهمزمان در صف اجرا میشه
        $orderData = $event->someData->toArray();

        // حالا شما می‌تونید داده‌ها رو در صف ارسال کنید بدون اینکه `PDO` باشه
        Log::info('Order confirmation for order ID: ' . $orderData['id']);
    }
}
