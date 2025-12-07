@extends('layouts.app')

@section('title', 'Dashboard Kasir - CafeIn')

@section('content')
    <section class="page-hero compact">
        <div class="page-hero-bg"></div>
        <div class="container">
            <div class="page-hero-content">
                <span class="page-badge">Kasir</span>
                <h1>Dashboard Kasir</h1>
                <p>UI kasir yang senada dengan pengalaman pengguna: cepat, jelas, dan fokus ke transaksi.</p>
                <div class="page-actions">
                    <a href="{{ route('menus') }}" class="btn btn-light btn-sm">Buka Menu</a>
                    <a href="{{ route('payments') }}" class="btn btn-primary btn-sm">Proses Pembayaran</a>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-grid">
                <a class="stat-card" href="{{ route('menus') }}" aria-label="Buat order baru">
                    <span class="stat-label">Order Baru</span>
                    <span class="stat-value">Menu</span>
                    <span class="stat-helper">Tambah item ke cart</span>
                </a>

                <a class="stat-card" href="{{ route('payments') }}" aria-label="Proses pembayaran">
                    <span class="stat-label">Proses Pembayaran</span>
                    <span class="stat-value">Payments</span>
                    <span class="stat-helper">QRIS / E-Wallet / Cash</span>
                </a>

                <a class="stat-card" href="{{ route('orders') }}" aria-label="Lihat pesanan berjalan">
                    <span class="stat-label">Pesanan Berjalan</span>
                    <span class="stat-value">{{ $pendingPayments->count() }}</span>
                    <span class="stat-helper">Pending / processing</span>
                </a>

                <a class="stat-card" href="{{ route('cart') }}" aria-label="Buka keranjang">
                    <span class="stat-label">Keranjang</span>
                    <span class="stat-value">Cart</span>
                    <span class="stat-helper">Review & checkout</span>
                </a>
            </div>

            <div class="dashboard-grid mt-3">
                <div class="stat-card" onclick="location.href='{{ route('payments') }}'" style="cursor:pointer;">
                    <span class="stat-label">Pendapatan Hari Ini</span>
                    <span class="stat-value">Rp {{ number_format((float) $todayRevenue, 0, ',', '.') }}</span>
                    <span class="stat-helper">Klik untuk detail</span>
                </div>
                <div class="stat-card" onclick="location.href='{{ route('orders') }}'" style="cursor:pointer;">
                    <span class="stat-label">Pesanan Selesai</span>
                    <span class="stat-value">{{ $ordersCompletedToday }}</span>
                    <span class="stat-helper">Hari ini</span>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="panel-header">
                    <div>
                        <h2>Menu Cepat</h2>
                        <span>Tap kartu untuk menambah ke keranjang</span>
                    </div>
                    <div class="page-actions">
                        <a href="{{ route('cart') }}" class="btn btn-light btn-sm">Buka Keranjang</a>
                    </div>
                </div>
                <div class="search-card">
                    <div class="search-card-header">
                        <div>
                            <div class="search-card-title">Cari Menu</div>
                            <div class="search-card-subtitle">Pencarian instan dengan tampilan yang sama seperti halaman user</div>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('kasir.dashboard') }}" class="kasir-menu-search">
                        <div class="search-row">
                            <div class="form-group search-main">
                                <label for="menu_q">Nama / deskripsi menu</label>
                                <div class="search-input-wrap">
                                    <span class="search-input-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <circle cx="11" cy="11" r="6"></circle>
                                            <line x1="16" y1="16" x2="21" y2="21"></line>
                                        </svg>
                                    </span>
                                    <input type="text" id="menu_q" name="menu_q" value="{{ $menuSearch ?? '' }}" placeholder="Cari menu favorit pelanggan">
                                </div>
                            </div>
                            <button type="submit" class="btn-secondary">Cari</button>
                        </div>
                    </form>
                </div>

                @if($quickMenus->isEmpty())
                    <p class="empty-state">Belum ada menu aktif.</p>
                @else
                    <div class="kasir-menu-grid">
                        @foreach ($quickMenus as $menu)
                            <div class="kasir-menu-card" data-menu-id="{{ $menu->id }}">
                                <div class="kasir-menu-main">
                                    <div class="kasir-menu-thumb">
                                        @if ($menu->image)
                                            <img src="{{ Storage::url($menu->image) }}" alt="{{ $menu->name }}" class="thumb">
                                        @else
                                            <div class="thumb fallback">{{ strtoupper(substr($menu->name, 0, 1)) }}</div>
                                        @endif
                                    </div>
                                    <div class="kasir-menu-text">
                                        <div class="kasir-menu-name">{{ $menu->name }}</div>
                                        <div class="kasir-menu-meta">
                                            <span class="kasir-menu-price">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</span>
                                            <span class="kasir-menu-stock">{{ $menu->stock > 0 ? 'Stok: '.$menu->stock : 'Habis' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn-primary btn-block btn-add" data-menu-id="{{ $menu->id }}" {{ $menu->stock < 1 ? 'disabled' : '' }}>
                                    {{ $menu->stock < 1 ? 'Out of Stock' : 'Tambah' }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="dashboard-columns">
                <div class="dashboard-panel">
                    <div class="panel-header">
                        <div>
                            <h2>Antrian Aktif</h2>
                            <span>Ketuk item untuk proses</span>
                        </div>
                        <div class="page-actions">
                            <a href="{{ route('orders') }}" class="btn btn-light btn-sm">Lihat semua</a>
                        </div>
                    </div>
                    <ul class="panel-list">
                        @forelse ($pendingPayments as $order)
                            <li>
                                <div>
                                    <strong>#{{ $order->order_number }}</strong>
                                    <p>{{ $order->user?->name ?? 'Guest' }}</p>
                                </div>
                                <div class="panel-meta">
                                    <span class="status-pill status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                                    <span class="price">Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</span>
                                </div>
                                <div class="page-actions" style="margin-left:12px;">
                                    @if ($order->status === 'pending')
                                        <a href="{{ url('/payments?order_id='.$order->id) }}" class="btn-secondary">Bayar</a>
                                    @endif
                                    @if ($order->status === 'paid')
                                        <form method="POST" action="{{ route('orders.status', $order) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="processing">
                                            <button type="submit" class="btn-secondary">Mulai Diproses</button>
                                        </form>
                                    @endif
                                    @if ($order->status === 'processing')
                                        <form method="POST" action="{{ route('orders.status', $order) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn-primary">Selesai</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('orders.track', $order) }}" class="btn-secondary">Track</a>
                                </div>
                            </li>
                        @empty
                            <li class="empty-state">Tidak ada antrian saat ini.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="dashboard-panel">
                    <div class="panel-header">
                        <div>
                            <h2>Pembayaran Terbaru</h2>
                            <span>Gaya kartu sama dengan timeline user</span>
                        </div>
                        <div class="page-actions">
                            <a href="{{ route('payments') }}" class="btn btn-light btn-sm">Detail</a>
                        </div>
                    </div>
                    <ul class="panel-list">
                        @forelse ($recentPayments as $payment)
                            <li onclick="location.href='{{ route('payments') }}'" style="cursor:pointer;">
                                <div>
                                    <strong>{{ strtoupper($payment->method ?? 'N/A') }}</strong>
                                    <p>#{{ $payment->order?->order_number ?? 'Tanpa Order' }} · {{ $payment->order?->user?->name ?? 'Guest' }}</p>
                                </div>
                                <div class="panel-meta">
                                    <span class="price">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</span>
                                    <span class="status-pill status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="empty-state">Belum ada transaksi.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endsection
