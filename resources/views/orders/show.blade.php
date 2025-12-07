@extends('layouts.app')

@section('title', 'Order Detail - CafeIn')

@section('content')
    <section class="page-hero compact">
        <div class="page-hero-bg"></div>
        <div class="container">
            <div class="page-hero-content">
                <span class="page-badge">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    Detail Pesanan
                </span>
                <h1>Order <span class="text-accent">#{{ $order->order_number }}</span></h1>
                <p>Ringkasan lengkap pesanan dan status pembayaran</p>
            </div>
        </div>
    </section>

    <section class="order-detail-section">
        <div class="container">
            @if (session('success'))
                <div class="alert-box alert-success">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="alert-box alert-error">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="order-status-banner status-{{ $order->status }}">
                <div class="status-banner-icon">
                    @if($order->status === 'pending')
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    @elseif($order->status === 'paid')
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                    @elseif($order->status === 'processing')
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"></path>
                        </svg>
                    @elseif($order->status === 'completed')
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    @else
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    @endif
                </div>
                <div class="status-banner-content">
                    <span class="status-banner-label">Status Pesanan</span>
                    <span class="status-banner-value">{{ ucfirst($order->status) }}</span>
                </div>
                <div class="status-banner-date">
                    <span>Diperbarui</span>
                    <strong>{{ $order->updated_at->diffForHumans() }}</strong>
                </div>
            </div>

            <div class="order-detail-grid">
                <div class="order-detail-main">
                    <div class="detail-card">
                        <div class="detail-card-header">
                            <div class="detail-card-icon">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                    <line x1="3" y1="6" x2="21" y2="6"></line>
                                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                                </svg>
                            </div>
                            <div>
                                <h2>Item Pesanan</h2>
                                <span class="detail-card-subtitle">{{ $order->orderItems->count() }} item dalam pesanan</span>
                            </div>
                        </div>
                        <div class="order-items-detail">
                            @foreach($order->orderItems as $item)
                                <div class="order-item-detail-row">
                                    <div class="item-detail-thumb">
                                        @if($item->menu && $item->menu->image)
                                            <img src="{{ Storage::url($item->menu->image) }}" alt="{{ $item->menu->name }}">
                                        @else
                                            <span>{{ strtoupper(substr($item->menu->name ?? 'I', 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div class="item-detail-info">
                                        <span class="item-detail-name">{{ $item->menu?->name ?? 'Item' }}</span>
                                        <span class="item-detail-qty">Qty: {{ $item->quantity }}</span>
                                    </div>
                                    <span class="item-detail-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="detail-card">
                        <div class="detail-card-header">
                            <div class="detail-card-icon">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                            </div>
                            <div>
                                <h2>Riwayat Pembayaran</h2>
                                <span class="detail-card-subtitle">{{ $order->payments->count() }} transaksi</span>
                            </div>
                        </div>
                        <div class="payment-history-list">
                            @forelse($order->payments as $payment)
                                <div class="payment-history-item">
                                    <div class="payment-method-badge">
                                        @if(strtolower($payment->method ?? '') === 'cash')
                                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        @else
                                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                                <line x1="1" y1="10" x2="23" y2="10"></line>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="payment-info">
                                        <span class="payment-method-name">{{ strtoupper($payment->method ?? '-') }}</span>
                                        <span class="payment-date">{{ $payment->paid_at?->format('d M Y, H:i') ?? 'Pending' }}</span>
                                    </div>
                                    <div class="payment-status-wrap">
                                        <span class="payment-status status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
                                        <span class="payment-amount">Rp {{ number_format((float)$payment->amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-payment-state">
                                    <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                        <line x1="1" y1="10" x2="23" y2="10"></line>
                                    </svg>
                                    <p>Belum ada pembayaran tercatat</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="order-detail-sidebar">
                    <div class="detail-card">
                        <div class="detail-card-header compact">
                            <div class="detail-card-icon small">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </div>
                            <h2>Informasi Pesanan</h2>
                        </div>
                        <div class="info-list">
                            <div class="info-row">
                                <span class="info-label">Order Number</span>
                                <span class="info-value">#{{ $order->order_number }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Tanggal Order</span>
                                <span class="info-value">{{ $order->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Pemesan</span>
                                <span class="info-value">{{ $order->metadata['customer_name'] ?? ($order->user?->name ?? 'Guest') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Catatan</span>
                                <span class="info-value note">{{ $order->metadata['notes'] ?? ($order->metadata['note'] ?? '-') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-card payment-summary-card">
                        <div class="detail-card-header compact">
                            <div class="detail-card-icon small">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                            <h2>Ringkasan Pembayaran</h2>
                        </div>
                        <div class="payment-summary-list">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="summary-row">
                                <span>Biaya Layanan</span>
                                <span>Rp {{ number_format($serviceFee, 0, ',', '.') }}</span>
                            </div>
                            <div class="summary-divider"></div>
                            <div class="summary-row total">
                                <span>Total Pembayaran</span>
                                <span class="total-amount">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    @php($roleName = auth()->user()->role?->name ?? null)
                    @if (in_array($roleName, ['Admin', 'Kasir'], true))
                        @if ($order->status === 'paid' || $order->status === 'processing')
                            <div class="admin-action-card">
                                <span class="admin-action-label">Aksi Admin</span>
                                @if ($order->status === 'paid')
                                    <form method="POST" action="{{ route('orders.status', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="processing">
                                        <button type="submit" class="btn btn-outline btn-block">
                                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"></path>
                                            </svg>
                                            Mulai Diproses
                                        </button>
                                    </form>
                                @elseif ($order->status === 'processing')
                                    <form method="POST" action="{{ route('orders.status', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                            Tandai Selesai
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    @endif

                    <div class="detail-actions">
                        <a class="btn btn-outline btn-block" href="{{ route('orders.track', $order) }}">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            Track Order
                        </a>
                        <a class="btn btn-primary btn-block" href="{{ route('orders') }}">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                            Kembali ke Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
