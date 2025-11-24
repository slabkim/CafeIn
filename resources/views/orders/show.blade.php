@extends('layouts.app')

@section('title', 'Order Detail - CafeIn')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>Order Detail</h1>
            <p>Detail pesanan #{{ $order->order_number }}</p>
        </div>
    </div>

    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-grid">
                <div class="dashboard-panel">
                    <div class="panel-header">
                        <h2>Informasi Pesanan</h2>
                        <span>Status: <span class="status-pill status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></span>
                    </div>
                    <ul class="panel-list">
                        <li>
                            <div><strong>Order Number</strong></div>
                            <div>{{ $order->order_number }}</div>
                        </li>
                        <li>
                            <div><strong>Tanggal</strong></div>
                            <div>{{ $order->created_at->format('d M Y, H:i') }}</div>
                        </li>
                        <li>
                            <div><strong>Pemesan</strong></div>
                            <div>{{ $order->user?->name ?? 'Guest' }}</div>
                        </li>
                        <li>
                            <div><strong>Catatan</strong></div>
                            <div>{{ $order->metadata['note'] ?? '-' }}</div>
                        </li>
                    </ul>
                </div>

                <div class="dashboard-panel">
                    <div class="panel-header">
                        <h2>Ringkasan Pembayaran</h2>
                        <span>{{ $order->currency ?? 'IDR' }}</span>
                    </div>
                    <ul class="panel-list">
                        <li>
                            <div>Subtotal</div>
                            <div>Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
                        </li>
                        <li>
                            <div>Biaya Layanan</div>
                            <div>Rp {{ number_format($serviceFee, 0, ',', '.') }}</div>
                        </li>
                        <li>
                            <div><strong>Total</strong></div>
                            <div><strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong></div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="panel-header">
                    <h2>Item Pesanan</h2>
                    <span>{{ $order->orderItems->count() }} item</span>
                </div>
                <div class="table-wrapper">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($order->orderItems as $item)
                            <tr>
                                <td>{{ $item->menu?->name ?? 'Item' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="panel-header">
                    <h2>Pembayaran</h2>
                    <span>{{ $order->payments->count() }} transaksi</span>
                </div>
                <ul class="panel-list">
                    @forelse($order->payments as $payment)
                        <li>
                            <div>
                                <strong>{{ strtoupper($payment->method ?? '-') }}</strong>
                                <p>{{ ucfirst($payment->status) }} &middot; {{ $payment->paid_at?->format('d M Y H:i') ?? '-' }}</p>
                            </div>
                            <div class="panel-meta">
                                <span class="price">Rp {{ number_format((float)$payment->amount, 0, ',', '.') }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="empty-state">Belum ada pembayaran.</li>
                    @endforelse
                </ul>
            </div>

            <div class="page-actions">
                <a class="btn-secondary" href="{{ route('orders.track', $order) }}">Track Order</a>
                <a class="btn-primary" href="{{ route('orders') }}">Kembali ke Orders</a>
            </div>
        </div>
    </section>
@endsection

