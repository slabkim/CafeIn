@extends('layouts.app')

@section('title', 'Track Order - CafeIn')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>Track Order</h1>
            <p>Lacak status #{{ $order->order_number }}</p>
        </div>
    </div>

    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h2>Status Pesanan</h2>
                    <span>Terakhir diperbarui: {{ $order->updated_at->diffForHumans() }}</span>
                </div>

                <ol class="track-steps">
                    @php
                        $current = $order->status;
                        $orderOf = collect(array_column($steps, 'key'));
                        $currentIndex = max(0, $orderOf->search($current));
                    @endphp
                    @foreach ($steps as $i => $s)
                        <li class="track-step {{ $i <= $currentIndex ? 'is-done' : '' }} {{ $i === $currentIndex ? 'is-current' : '' }}">
                            <span class="step-index">{{ $i + 1 }}</span>
                            <div class="step-content">
                                <div class="step-title">{{ $s['label'] }}</div>
                                @if ($s['key'] === 'paid' && $order->paid_at)
                                    <div class="step-meta">Dibayar pada {{ $order->paid_at->format('d M Y H:i') }}</div>
                                @elseif ($s['key'] === $current)
                                    <div class="step-meta">Status saat ini</div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="dashboard-panel">
                <div class="panel-header">
                    <h2>Ringkasan</h2>
                    <span>Total</span>
                </div>
                <ul class="panel-list">
                    <li>
                        <div>Nomor Order</div>
                        <div>#{{ $order->order_number }}</div>
                    </li>
                    <li>
                        <div>Status</div>
                        <div><span class="status-pill status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></div>
                    </li>
                    <li>
                        <div>Total</div>
                        <div><strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong></div>
                    </li>
                </ul>
            </div>

            <div class="page-actions">
                <a class="btn-secondary" href="{{ route('orders.show', $order) }}">Lihat Detail</a>
                <a class="btn-primary" href="{{ route('orders') }}">Kembali ke Orders</a>
            </div>
        </div>
    </section>
@endsection

