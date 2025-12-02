@extends('layouts.app')

@section('title', 'Payment - CafeIn')

@section('content')
    <!-- Page Header -->
    <section class="page-hero compact">
        <div class="page-hero-bg"></div>
        <div class="container">
            <div class="page-hero-content">
                <span class="page-badge">Pembayaran</span>
                <h1>Lengkapi <span class="text-accent">Pembayaran</span></h1>
                <p>Selesaikan pesanan dan pilih metode pembayaran favorit Anda</p>
            </div>
        </div>
    </section>

    <section class="payment-section">
        <div class="container">
            <div class="payment-layout">
                @php $role = auth()->user()->role?->name ?? null; @endphp
                @if(in_array($role, ['Kasir','Admin']) && empty($order))
                    <div class="dashboard-panel" style="margin-bottom:16px;">
                        <div class="panel-header">
                            <h2>Pilih Order untuk Diproses</h2>
                            <span>{{ isset($pendingOrders) ? $pendingOrders->count() : 0 }} antrian</span>
                        </div>
                        <ul class="panel-list">
                            @forelse(($pendingOrders ?? collect()) as $po)
                                <li>
                                    <div>
                                        <strong>#{{ $po->order_number }}</strong>
                                        <p>{{ $po->user?->name ?? 'Guest' }} &middot; {{ $po->created_at->format('d M Y H:i') }}</p>
                                    </div>
                                    <div class="panel-meta">
                                        <span class="price">Rp {{ number_format((float) $po->total_price, 0, ',', '.') }}</span>
                                        <a href="{{ url('/payments?order_id='.$po->id) }}" class="btn-secondary">Proses</a>
                                    </div>
                                </li>
                            @empty
                                <li class="empty-state">Tidak ada order pending/processing.</li>
                            @endforelse
                        </ul>
                    </div>
                @endif
                <!-- Payment Form -->
                <div class="payment-form-container">
                    <div class="payment-card">
                        <h2>Payment Method</h2>

                        <!-- Payment Methods -->
                        <div class="payment-methods">
                            @foreach($paymentMethods as $method)
                                <div class="payment-method {{ $loop->first ? 'active' : '' }}">
                                    <input type="radio" name="payment" id="method-{{ $method['key'] }}" value="{{ $method['key'] }}" {{ $loop->first ? 'checked' : '' }}>
                                    <label for="method-{{ $method['key'] }}">
                                        <span class="method-icon" aria-hidden="true">
                                            @switch($method['key'])
                                                @case('qris')
                                                    <svg viewBox="0 0 24 24">
                                                        <rect x="4" y="4" width="16" height="16" rx="3"></rect>
                                                        <rect x="7" y="7" width="4" height="4"></rect>
                                                        <rect x="13" y="7" width="4" height="4"></rect>
                                                        <rect x="7" y="13" width="4" height="4"></rect>
                                                        <path d="M13 13h4v4h-2"></path>
                                                    </svg>
                                                    @break
                                                @case('gopay')
                                                    <svg viewBox="0 0 24 24">
                                                        <circle cx="12" cy="12" r="8"></circle>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                    @break
                                                @case('ovo')
                                                    <svg viewBox="0 0 24 24">
                                                        <circle cx="12" cy="12" r="8"></circle>
                                                        <circle cx="12" cy="12" r="5.5"></circle>
                                                    </svg>
                                                    @break
                                                @case('cash')
                                                    <svg viewBox="0 0 24 24">
                                                        <rect x="4" y="7" width="16" height="10" rx="2"></rect>
                                                        <line x1="4" y1="11" x2="20" y2="11"></line>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                    @break
                                                @default
                                                    <svg viewBox="0 0 24 24">
                                                        <circle cx="12" cy="12" r="8"></circle>
                                                    </svg>
                                            @endswitch
                                        </span>
                                        <div class="method-info">
                                            <strong>{{ $method['label'] }}</strong>
                                            <span>{{ $method['description'] }}</span>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        @if($order)
                            @php $isPaymentCompleted = in_array($order->status, ['paid', 'completed']); @endphp
                            <!-- Delivery Information -->
                            <div class="delivery-info">
                                <h3>Order Information</h3>
                                <div class="delivery-form">
                                    <div class="form-group">
                                        <label>Order Number</label>
                                        <input type="text" value="{{ $order->order_number }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <input type="text" value="{{ ucfirst($order->status) }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Customer</label>
                                        @if(in_array($role, ['Kasir','Admin']))
                                            <div style="display:flex; gap:8px; align-items:center;">
                                                <input type="text" id="cf-customer-name" value="{{ $order->metadata['customer_name'] ?? ($order->user?->name ?? 'Guest') }}" placeholder="Nama Customer" style="flex:1;">
                                                <small id="cf-customer-status" class="stat-helper" aria-live="polite"></small>
                                            </div>
                                        @else
                                            <input type="text" value="{{ $order->user?->name ?? 'Guest' }}" readonly>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <div style="display:flex; gap:8px; align-items:center;">
                                            <textarea
                                                rows="3"
                                                id="cf-notes"
                                                placeholder="Catatan untuk pesanan (mis. less sugar, extra ice)"
                                                style="flex:1;"
                                                {{ $isPaymentCompleted ? 'readonly' : '' }}
                                            >{{ $order->metadata['notes'] ?? ($order->metadata['note'] ?? '') }}</textarea>
                                            <small id="cf-notes-status" class="stat-helper" aria-live="polite"></small>
                                        </div>
                                    </div>
                                </div>
                                @if(in_array($role, ['Kasir','Admin']))
                                    <div class="page-actions" style="margin-top:8px;">
                                        <button type="button" id="btn-save-order-info" class="btn-secondary">Simpan Info</button>
                                    </div>
                                @endif
                            </div>
                            @if($isPaymentCompleted)
                                <p class="summary-note" role="status">Pembayaran untuk pesanan ini sudah tercatat.</p>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="order-summary-container">
                    <div class="order-summary-card">
                        <h2>Order Summary</h2>

                        @if($order)

                            <div class="summary-items">
                                @foreach($order->orderItems as $item)
                                    <div class="summary-item">
                                        <div class="item-details">
                                            <span class="item-name">{{ $item->menu->name }}</span>
                                            <span class="item-qty">× {{ $item->quantity }}</span>
                                        </div>
                                        <span class="item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-calculations">
                                <div class="calc-row">
                                    <span>Subtotal</span>
                                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                                @if($serviceFee > 0)
                                    <div class="calc-row">
                                        <span>Service Fee</span>
                                        <span>Rp {{ number_format($serviceFee, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div class="calc-row">
                                    <span>Currency</span>
                                    <span>{{ $order->currency }}</span>
                                </div>
                                @if($latestPayment)
                                    <div class="calc-row">
                                        <span>Last Payment</span>
                                        <span>{{ strtoupper($latestPayment->method) }} ({{ $latestPayment->status }})</span>
                                    </div>
                                @endif
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-total">
                                <span>Total Payment</span>
                                <span class="total-amount">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>

                            <button class="btn-payment btn btn-primary" type="button" data-order-id="{{ $order->id }}" {{ $isPaymentCompleted ? 'disabled' : '' }}>
                                {{ $isPaymentCompleted ? 'Payment Completed' : 'Complete Payment' }}
                            </button>

                            <div class="secure-payment">
                                <span>🔒</span>
                                <span>Secure payment with SSL encryption</span>
                            </div>
                        @else
                            <p class="empty-message">Belum ada pesanan yang perlu dibayar.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    {{-- Midtrans Snap JS (Sandbox/Production mengikuti config) --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('midtrans.client_key') }}"></script>
    @if($order)
        <script>
            window.CafeInPayment = {
                orderId: {{ $order->id }},
                completeUrl: @json(route('payments.complete')),
                saveUrl: @json(route('payments.saveInfo')),
                snapUrl: @json(route('payments.snapToken', ['order_id' => $order->id])),
            };
        </script>
    @endif
@endsection

