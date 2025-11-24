<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\View\View;

class CashierDashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();

        $pendingPayments = Order::with('user')
            ->whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at')
            ->limit(8)
            ->get();

        $recentPayments = Payment::with(['order.user'])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $awaitingConfirmation = Payment::with(['order.user'])
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        $todayRevenue = Payment::whereDate('paid_at', $today)
            ->where('status', 'success')
            ->sum('amount');

        $ordersCompletedToday = Order::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->count();

        return view('dashboard.kasir', [
            'pendingPayments' => $pendingPayments,
            'recentPayments' => $recentPayments,
            'awaitingConfirmation' => $awaitingConfirmation,
            'todayRevenue' => $todayRevenue,
            'ordersCompletedToday' => $ordersCompletedToday,
        ]);
    }
}