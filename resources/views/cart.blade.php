@extends('layouts.app')

@section('title', 'Cart - CafeIn')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>Your Cart</h1>
            <p>Review and adjust your items before completing the order.</p>
        </div>
    </div>

    <section class="cart-section">
        <div class="container">
            @if (count($cartItems) > 0)
                <div class="cart-layout">
                    <div class="cart-list">
                        @foreach ($cartItems as $item)
                            <article class="cart-item" data-cart-item-id="{{ $item['cart_item_id'] }}"
                                data-unit-price="{{ $item['menu']->price }}" data-price="{{ $item['menu']->price }}">
                                <div class="cart-item-thumb">
                                    <span>{{ strtoupper(substr($item['menu']->name, 0, 1)) }}</span>
                                </div>
                                <div class="cart-item-body">
                                    <div class="cart-item-header">
                                        <h3>{{ $item['menu']->name }}</h3>
                                        <button type="button" class="cart-remove-btn" data-action="remove"
                                            aria-label="Remove {{ $item['menu']->name }} from cart">
                                            Remove
                                        </button>
                                    </div>
                                    <p class="cart-item-desc">
                                        {{ $item['menu']->description ?? 'Nikmati sajian terbaik dari CafeIn.' }}
                                    </p>
                                    <div class="cart-item-footer">
                                        <div class="quantity-controls" role="group" aria-label="Adjust quantity">
                                            <button type="button" class="qty-btn" data-action="decrease"
                                                aria-label="Decrease quantity">-</button>
                                            <input type="number" class="quantity-input" value="{{ $item['quantity'] }}"
                                                min="1" aria-label="Quantity"
                                                data-cart-item-id="{{ $item['cart_item_id'] }}">
                                            <button type="button" class="qty-btn" data-action="increase"
                                                aria-label="Increase quantity">+</button>
                                        </div>
                                        <div class="cart-item-pricing">
                                            <span class="unit-price">Rp
                                                {{ number_format($item['menu']->price, 0, ',', '.') }} / item</span>
                                            <span class="item-price" data-item-subtotal>
                                                Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <aside class="cart-summary-card">
                        <h2>Order Summary</h2>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span class="summary-value" data-cart-total>Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Service Fee ({{ (int) ($serviceFeeRate * 100) }}%)</span>
                            <span class="summary-value" data-cart-service>Rp
                                {{ number_format($serviceFee, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-row summary-total">
                            <span>Total Payment</span>
                            <span class="summary-value" data-cart-grand>Rp
                                {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                        <button class="btn-primary checkout-btn" type="button">Proceed to Checkout</button>
                        <p class="summary-note">Selesaikan pembayaran di halaman Payments setelah memastikan data sudah
                            benar.</p>
                    </aside>
                </div>
            @else
                <div class="empty-cart">
                    <p>Your cart is empty.</p>
                    <a href="{{ route('menus') }}" class="btn-primary">Browse Menu</a>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.CafeInCart = {
            baseTotal: {{ (float) $total }},
            serviceFeeRate: {{ $serviceFeeRate }},
            menuUrl: @json(route('menus')),
            paymentsUrl: @json(route('payments')),
            checkoutUrl: @json(route('cart.checkout')),
        };
    </script>
@endsection
