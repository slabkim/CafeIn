<?php

namespace App\Console;

use App\Models\Order;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Batalkan otomatis pesanan yang belum dibayar lebih dari 15 menit
        $schedule->call(function () {
            $threshold = now()->subMinutes(15);

            Order::with('payments')
                ->where('status', 'pending')
                ->whereNull('paid_at')
                ->where('created_at', '<=', $threshold)
                ->orderBy('id')
                ->chunkById(100, function ($orders) {
                    foreach ($orders as $order) {
                        DB::transaction(function () use ($order) {
                            foreach ($order->payments as $payment) {
                                if ($payment->status === 'pending') {
                                    $payment->update(['status' => 'failed']);
                                }
                            }

                            $meta = is_array($order->metadata) ? $order->metadata : [];
                            $meta['auto_cancelled_at'] = now();
                            $meta['auto_cancel_reason'] = 'expired_unpaid_15m';

                            $order->update([
                                'status' => 'cancelled',
                                'metadata' => $meta,
                            ]);
                        });
                    }
                });
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
