@extends('layouts.app')

@section('title', 'My Orders - CafeIn')

@section('content')
    <!-- Page Header -->
    <section class="page-hero compact">
        <div class="page-hero-bg"></div>
        <div class="container">
            <div class="page-hero-content">
                <span class="page-badge">Riwayat</span>
                <h1>Pesanan <span class="text-accent">Saya</span></h1>
                <p>Pantau dan kelola semua pesanan Anda</p>
            </div>
        </div>
    </section>

    <!-- Orders Section -->
    <section class="orders-section-modern">
        <div class="container">
            <!-- Order Tabs -->
            <div class="order-tabs-modern">
                <button class="order-tab tab-btn active" data-tab="current">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>Pesanan Aktif</span>
                    @if (count($currentOrders) > 0)
                        <span class="tab-badge">{{ count($currentOrders) }}</span>
                    @endif
                </button>
                <button class="order-tab tab-btn" data-tab="history">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path>
                    </svg>
                    <span>Riwayat</span>
                </button>
            </div>

            @php
                $statusClasses = [
                    'pending' => 'status-pending',
                    'paid' => 'status-paid',
                    'processing' => 'status-processing',
                    'completed' => 'status-completed',
                    'cancelled' => 'status-cancelled',
                ];
                $statusIcons = [
                    'pending' =>
                        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>',
                    'paid' =>
                        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
                    'processing' =>
                        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"></path></svg>',
                    'completed' =>
                        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
                    'cancelled' =>
                        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
                ];
            @endphp

            <!-- Current Orders -->
            <div class="tab-content active" id="current">
                <div class="orders-grid">
                    @forelse($currentOrders as $order)
                        <div class="order-card-modern">
                            <div class="order-card-header">
                                <div class="order-info">
                                    <span class="order-number">#{{ $order->order_number }}</span>
                                    <span class="order-date">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    <span
                                        class="order-customer">{{ $order->metadata['customer_name'] ?? ($order->user?->name ?? 'Guest') }}</span>
                                </div>
                                <span class="order-status {{ $statusClasses[$order->status] ?? 'status-pending' }}">
                                    {!! $statusIcons[$order->status] ?? '' !!}
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            <div class="order-card-body">
                                <div class="order-items-list">
                                    @foreach ($order->orderItems->take(3) as $item)
                                        <div class="order-item-row">
                                            <div class="item-thumb">
                                                @if ($item->menu->image)
                                                    <img src="{{ Storage::url($item->menu->image) }}" alt="{{ $item->menu->name }}">
                                                @else
                                                    <span>{{ strtoupper(substr($item->menu->name, 0, 1)) }}</span>
                                                @endif
                                            </div>
                                            <div class="item-info">
                                                <span class="item-name">{{ $item->menu->name }}</span>
                                                <span class="item-qty">x{{ $item->quantity }}</span>
                                            </div>
                                            <span class="item-price">Rp
                                                {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                    @if ($order->orderItems->count() > 3)
                                        <span class="more-items">+{{ $order->orderItems->count() - 3 }} item lainnya</span>
                                    @endif
                                </div>
                            </div>
                            <div class="order-card-footer">
                                <div class="order-total">
                                    <span class="total-label">Total</span>
                                    <span class="total-price">Rp
                                        {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                </div>
                                <div class="order-actions">
                                    <a href="{{ route('orders.track', $order) }}" class="btn btn-outline btn-sm">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        Track
                                    </a>
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-primary btn-sm">Detail</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state full-width">
                            <div class="empty-icon">
                                <svg viewBox="0 0 24 24" width="64" height="64" fill="none" stroke="currentColor"
                                    stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="3" y1="9" x2="21" y2="9"></line>
                                    <line x1="9" y1="21" x2="9" y2="9"></line>
                                </svg>
                            </div>
                            <h3>Belum Ada Pesanan Aktif</h3>
                            <p>Anda belum memiliki pesanan yang sedang diproses saat ini.</p>
                            <a href="{{ route('menus') }}" class="btn btn-primary">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M4 8h12v5a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8z"></path>
                                    <path d="M16 10h2a3 3 0 0 1 0 6h-2"></path>
                                </svg>
                                Pesan Sekarang
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Order History -->
            <div class="tab-content" id="history">
                <div class="orders-grid">
                    @forelse($orderHistory as $order)
                        <div class="order-card-modern">
                            <div class="order-card-header">
                                <div class="order-info">
                                    <span class="order-number">#{{ $order->order_number }}</span>
                                    <span class="order-date">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    <span
                                        class="order-customer">{{ $order->metadata['customer_name'] ?? ($order->user?->name ?? 'Guest') }}</span>
                                </div>
                                <span class="order-status {{ $statusClasses[$order->status] ?? 'status-completed' }}">
                                    {!! $statusIcons[$order->status] ?? '' !!}
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            <div class="order-card-body">
                                <div class="order-items-list">
                                    @foreach ($order->orderItems->take(3) as $item)
                                        <div class="order-item-row">
                                            <div class="item-thumb">
                                                @if ($item->menu->image)
                                                    <img src="{{ Storage::url($item->menu->image) }}" alt="{{ $item->menu->name }}">
                                                @else
                                                    <span>{{ strtoupper(substr($item->menu->name, 0, 1)) }}</span>
                                                @endif
                                            </div>
                                            <div class="item-info">
                                                <span class="item-name">{{ $item->menu->name }}</span>
                                                <span class="item-qty">x{{ $item->quantity }}</span>
                                            </div>
                                            <span class="item-price">Rp
                                                {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                    @if ($order->orderItems->count() > 3)
                                        <span class="more-items">+{{ $order->orderItems->count() - 3 }} item
                                            lainnya</span>
                                    @endif
                                </div>
                            </div>
                            <div class="order-card-footer">
                                <div class="order-total">
                                    <span class="total-label">Total</span>
                                    <span class="total-price">Rp
                                        {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                </div>
                                <div class="order-actions">
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-outline btn-sm">Lihat
                                        Detail</a>
                                    @if ($order->status === 'completed')
                                        <button class="btn btn-primary btn-sm" data-reorder="{{ $order->id }}">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <polyline points="23 4 23 10 17 10"></polyline>
                                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                                            </svg>
                                            Pesan Lagi
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state full-width">
                            <div class="empty-icon">
                                <svg viewBox="0 0 24 24" width="64" height="64" fill="none"
                                    stroke="currentColor" stroke-width="1.5">
                                    <path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path>
                                </svg>
                            </div>
                            <h3>Riwayat Kosong</h3>
                            <p>Riwayat pesanan Anda akan muncul di sini setelah pesanan selesai.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
