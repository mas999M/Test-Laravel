<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class DeleteOldPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-pending-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Order::where('status', 'pending')
            ->where('created_at', '<', now()->subSecond(10)) // <-- ۱۲ ساعت
            ->each(function($order) {
                // حذف کامل پرداخت‌ها
                $order->payments()->update(['status' => 'failed']);

                // حذف کامل سفارش
                $order->update(['status' => 'failed']);
            });

        $this->info('Old pending orders and payments older than 12 hours deleted.');
    }
}
