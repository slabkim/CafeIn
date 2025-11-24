<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();
        $lastSevenDays = now()->subDays(6)->startOfDay();

        // Sum only successful payments (completed transactions)
        $totalRevenue = Payment::where('status', 'success')->sum('amount');
        $ordersToday = Order::whereDate('created_at', $today)->count();
        $pendingOrders = Order::whereIn('status', ['pending', 'processing'])->count();
        $newCustomers = User::whereHas('role', function ($query) {
            $query->where('name', 'Customer');
        })->where('created_at', '>=', $lastSevenDays)->count();

        $topMenus = Menu::select(
            'menus.id',
            'menus.name',
            'menus.price',
            'menus.stock',
            DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
        )
            ->leftJoin('order_items', 'menus.id', '=', 'order_items.menu_id')
            ->groupBy('menus.id', 'menus.name', 'menus.price', 'menus.stock')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $recentOrders = Order::with('user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Breakdown for completed transactions only
        $paymentBreakdown = Payment::select(
            'method',
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->where('status', 'success')
            ->whereNotNull('method')
            ->groupBy('method')
            ->orderByDesc('total_amount')
            ->get();

        $ordersByStatus = Order::select(
            'status',
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('dashboard.admin', [
            'totalRevenue' => $totalRevenue,
            'ordersToday' => $ordersToday,
            'pendingOrders' => $pendingOrders,
            'newCustomers' => $newCustomers,
            'topMenus' => $topMenus,
            'recentOrders' => $recentOrders,
            'paymentBreakdown' => $paymentBreakdown,
            'ordersByStatus' => $ordersByStatus,
        ]);
    }
}
