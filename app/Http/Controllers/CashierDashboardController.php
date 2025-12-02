<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashierDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $today = now()->toDateString();

        $pendingPayments = Order::with('user')
            ->whereIn('status', ['pending', 'paid', 'processing'])
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

        $menuSearch = trim((string) $request->query('menu_q', ''));

        $quickMenusQuery = Menu::where('is_active', true);
        if ($menuSearch !== '') {
            $lq = mb_strtolower($menuSearch);
            $quickMenusQuery->where(function ($q) use ($lq) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$lq}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$lq}%"]);
            });
        }

        $quickMenus = $quickMenusQuery
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view('dashboard.kasir', [
            'pendingPayments' => $pendingPayments,
            'recentPayments' => $recentPayments,
            'awaitingConfirmation' => $awaitingConfirmation,
            'todayRevenue' => $todayRevenue,
            'ordersCompletedToday' => $ordersCompletedToday,
            'quickMenus' => $quickMenus,
            'menuSearch' => $menuSearch,
        ]);
    }
}
