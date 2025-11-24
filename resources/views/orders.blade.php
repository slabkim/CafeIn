@extends('layouts.app')

@section('title', 'My Orders - CafeIn')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>My Orders</h1>
            <p>Track and manage your orders</p>
        </div>
    </div>

    <section class="orders-section">
        <div class="container">
            <!-- Order Tabs -->
            <div class="order-tabs">
                <button class="tab-btn active" data-tab="current">Current Orders</button>
                <button class="tab-btn" data-tab="history">Order History</button>
            </div>

            <!-- Current Orders -->
            <div class="tab-content active" id="current">
                <div class="orders-list">
                    @php
                        $statusClasses = [
                            'pending' => 'status-pending',
                            'paid' => 'status-paid',
                            'processing' => 'status-preparing',
                            'completed' => 'status-completed',
                            'cancelled' => 'status-cancelled',
                        ];
                    @endphp

                    @forelse($currentOrders as $order)
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <span class="order-number">#{{ $order->order_number }}</span>
                                    <span class="order-status {{ $statusClasses[$order->status] ?? 'status-pending' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <span class="order-date">{{ $order->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="order-items">
                                @foreach($order->orderItems as $item)
                                    <div class="order-item">
                                        <span class="item-name">{{ $item->menu->name }} × {{ $item->quantity }}</span>
                                        <span class="item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="order-footer">
                                <div class="order-total">
                                    <span>Total:</span>
                                    <span class="total-price">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                </div>
                                <a class="btn-secondary" href="{{ route('orders.track', $order) }}">Track Order</a>
                                <a class="btn-primary" href="{{ route('orders.show', $order) }}">Detail</a>
                            </div>
                        </div>
                    @empty
                        <p class="empty-message">Belum ada pesanan aktif saat ini.</p>
                    @endforelse
                </div>
            </div>

            <!-- Order History -->
            <div class="tab-content" id="history">
                <div class="orders-list">
                    @forelse($orderHistory as $order)
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <span class="order-number">#{{ $order->order_number }}</span>
                                    <span class="order-status {{ $statusClasses[$order->status] ?? 'status-completed' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <span class="order-date">{{ $order->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="order-items">
                                @foreach($order->orderItems as $item)
                                    <div class="order-item">
                                        <span class="item-name">{{ $item->menu->name }} × {{ $item->quantity }}</span>
                                        <span class="item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="order-footer">
                                <div class="order-total">
                                    <span>Total:</span>
                                    <span class="total-price">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                </div>
                                <a class="btn-secondary" href="{{ route('orders.show', $order) }}">View Detail</a>
                            </div>
                        </div>
                    @empty
                        <p class="empty-message">Riwayat pesanan belum tersedia.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
