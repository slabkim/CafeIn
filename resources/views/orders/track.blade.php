@extends('layouts.app')

@section('title', 'Track Order - CafeIn')

@section('content')
    <section class="page-hero compact">
        <div class="page-hero-bg"></div>
        <div class="container">
            <div class="page-hero-content">
                <span class="page-badge">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Lacak Pesanan
                </span>
                <h1>Order <span class="text-accent">#{{ $order->order_number }}</span></h1>
                <p>Pantau progres pesanan Anda secara real-time</p>
            </div>
        </div>
    </section>

    <section class="track-order-section">
        <div class="container">
            <div class="track-order-grid">
                <div class="track-main-panel">
                    <div class="track-card">
                        <div class="track-card-header">
                            <div class="track-header-icon">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                </svg>
                            </div>
                            <div class="track-header-info">
                                <h2>Status Pesanan</h2>
                                <span>Terakhir diperbarui: {{ $order->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="track-timeline">
                            @php
                                $current = $order->status;
                                $orderOf = collect(array_column($steps, 'key'));
                                $currentIndex = max(0, $orderOf->search($current));
                            @endphp

                            @foreach ($steps as $i => $s)
                                @php
                                    $isDone = $i < $currentIndex;
                                    $isCurrent = $i === $currentIndex;
                                    $isPending = $i > $currentIndex;
                                @endphp
                                <div class="timeline-item {{ $isDone ? 'is-done' : '' }} {{ $isCurrent ? 'is-current' : '' }} {{ $isPending ? 'is-pending' : '' }}">
                                    <div class="timeline-marker">
                                        @if($isDone)
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        @elseif($isCurrent)
                                            <span class="current-dot"></span>
                                        @else
                                            <span class="pending-dot"></span>
                                        @endif
                                    </div>
                                    <div class="timeline-connector"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">{{ $s['label'] }}</div>
                                        @if ($s['key'] === 'paid' && $order->paid_at)
                                            <div class="timeline-meta">
                                                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 6 12 12 16 14"></polyline>
                                                </svg>
                                                Dibayar pada {{ $order->paid_at->format('d M Y H:i') }}
                                            </div>
                                        @elseif ($s['key'] === $current)
                                            <div class="timeline-meta current">
                                                <span class="pulse-dot"></span>
                                                Status saat ini
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($order->status === 'processing')
                        <div class="estimate-card">
                            <div class="estimate-icon">
                                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </div>
                            <div class="estimate-content">
                                <span class="estimate-label">Estimasi Waktu</span>
                                <span class="estimate-value">10 - 15 menit</span>
                            </div>
                            <div class="estimate-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill"></div>
                                </div>
                                <span class="progress-text">Sedang diproses...</span>
                            </div>
                        </div>
                    @endif
                </div>

                @php($roleName = auth()->user()->role?->name ?? null)
                <div class="track-sidebar">
                    <div class="summary-card">
                        <div class="summary-card-header">
                            <h3>Ringkasan Pesanan</h3>
                        </div>
                        <div class="summary-info-list">
                            <div class="summary-info-row">
                                <span class="summary-info-label">Nomor Order</span>
                                <span class="summary-info-value">#{{ $order->order_number }}</span>
                            </div>
                            <div class="summary-info-row">
                                <span class="summary-info-label">Status</span>
                                <span class="order-status-badge status-{{ $order->status }}">
                                    @if($order->status === 'pending')
                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                    @elseif($order->status === 'paid')
                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    @elseif($order->status === 'processing')
                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"></path>
                                        </svg>
                                    @elseif($order->status === 'completed')
                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    @else
                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg>
                                    @endif
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            <div class="summary-info-row">
                                <span class="summary-info-label">Total Item</span>
                                <span class="summary-info-value">{{ $order->orderItems->count() }} item</span>
                            </div>
                            <div class="summary-divider"></div>
                            <div class="summary-info-row total">
                                <span class="summary-info-label">Total Pembayaran</span>
                                <span class="summary-total-value">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="track-actions">
                        <a class="btn btn-outline btn-block" href="{{ route('orders.show', $order) }}">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                            Lihat Detail Pesanan
                        </a>
                        <a class="btn btn-primary btn-block" href="{{ route('orders') }}">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                            Kembali ke Orders
                        </a>
                    </div>

                    @unless(in_array($roleName, ['Admin', 'Kasir'], true))
                        <div class="help-card">
                            <div class="help-icon">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                </svg>
                            </div>
                            <div class="help-content">
                                <span class="help-title">Butuh Bantuan?</span>
                                <span class="help-text">Hubungi kasir jika ada pertanyaan tentang pesanan Anda.</span>
                            </div>
                        </div>
                    @endunless
                </div>
            </div>
        </div>
    </section>
@endsection
