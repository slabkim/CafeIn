<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();

        $currentQuery = Order::with(['orderItems.menu', 'payments'])
            ->current()
            ->orderByDesc('created_at');

        $historyQuery = Order::with(['orderItems.menu', 'payments'])
            ->history()
            ->orderByDesc('created_at');

        if ($user && $user->role?->name === 'Customer') {
            $currentQuery->where('user_id', $user->id);
            $historyQuery->where('user_id', $user->id);
        }

        $currentOrders = $currentQuery->get();
        $orderHistory = $historyQuery->get();

        return view('orders', [
            'currentOrders' => $currentOrders,
            'orderHistory' => $orderHistory,
        ]);
    }

    public function show(Order $order): View|RedirectResponse
    {
        $user = Auth::user();
        if ($user && $user->role?->name === 'Customer' && $order->user_id !== $user->id) {
            abort(403);
        }

        $order->load(['orderItems.menu', 'payments', 'user']);

        $metadata = is_array($order->metadata) ? $order->metadata : [];
        $serviceFee = (float)($metadata['service_fee'] ?? 0);
        $subtotal = (float)($metadata['subtotal'] ?? ($order->total_price - $serviceFee));

        return view('orders.show', [
            'order' => $order,
            'subtotal' => $subtotal,
            'serviceFee' => $serviceFee,
        ]);
    }

    public function track(Order $order): View
    {
        $user = Auth::user();
        if ($user && $user->role?->name === 'Customer' && $order->user_id !== $user->id) {
            abort(403);
        }

        $order->load(['payments']);

        $steps = [
            ['key' => 'pending', 'label' => 'Menunggu Pembayaran'],
            ['key' => 'paid', 'label' => 'Pembayaran Diterima'],
            ['key' => 'processing', 'label' => 'Sedang Diproses'],
            ['key' => 'completed', 'label' => 'Selesai'],
        ];

        return view('orders.track', [
            'order' => $order,
            'steps' => $steps,
        ]);
    }
}
