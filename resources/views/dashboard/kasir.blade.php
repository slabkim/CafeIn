@extends('layouts.app')

@section('title', 'Kasir Console - CafeIn')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>Kasir Console</h1>
            <p>Tampilan ringkas untuk navigasi cepat.</p>
        </div>
    </div>

    <section class="dashboard-section">
        <div class="container">
            <!-- Quick Actions: satu layar untuk semua navigasi -->
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

            <!-- Ringkas: Hari ini -->
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

            <!-- Antrian aktif (klik navigasi) -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h2>Antrian Aktif</h2>
                    <span>Ketuk item untuk proses</span>
                </div>
                <ul class="panel-list">
                    @forelse ($pendingPayments as $order)
                        <li onclick="location.href='{{ route('payments') }}'" style="cursor:pointer;">
                            <div>
                                <strong>#{{ $order->order_number }}</strong>
                                <p>{{ $order->user?->name ?? 'Guest' }}</p>
                            </div>
                            <div class="panel-meta">
                                <span class="status-pill status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                                <span class="price">Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="empty-state">Tidak ada antrian saat ini.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Pembayaran terbaru (klik navigasi) -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h2>Pembayaran Terbaru</h2>
                    <span>Ketuk untuk detail</span>
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
    </section>
@endsection
