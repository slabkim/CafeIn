@extends('layouts.app')

@section('title', 'Home - CafeIn')

@section('content')
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">Welcome to CafeIn</h1>
            <p class="hero-subtitle">Experience the perfect blend of taste and ambiance</p>
            <a href="{{ route('menus') }}" class="btn btn-primary">Explore Menu</a>
        </div>
        <div class="hero-image">
            <svg class="hero-illustration" viewBox="0 0 120 120" aria-hidden="true">
                <rect x="28" y="42" width="64" height="44" rx="12" ry="12"></rect>
                <path d="M92 50h10a10 10 0 0 1 0 20H92"></path>
                <line x1="40" y1="90" x2="80" y2="90"></line>
                <path d="M52 28c0-8 6-10 6-18"></path>
                <path d="M66 28c0-8 6-10 6-18"></path>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose CafeIn?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 8h12v5a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8z"></path>
                            <path d="M16 10h2a3 3 0 0 1 0 6h-2"></path>
                            <path d="M6 19h10"></path>
                        </svg>
                    </div>
                    <h3>Premium Coffee</h3>
                    <p>Carefully selected beans from the best farms.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 12a6 6 0 0 1 18 0c0 3.31-4.03 6-9 6s-9-2.69-9-6z"></path>
                            <path d="M7 12a5 5 0 0 0 10 0"></path>
                        </svg>
                    </div>
                    <h3>Fresh Pastries</h3>
                    <p>Baked daily with love and quality ingredients.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 11l9-7 9 7v9a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2v-4H9v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        </svg>
                    </div>
                    <h3>Cozy Atmosphere</h3>
                    <p>Perfect place to work, study, or relax.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 13l3-6h7l-3 6h5l-8 9 2-7H4z"></path>
                        </svg>
                    </div>
                    <h3>Fast Delivery</h3>
                    <p>Quick and reliable service to your doorstep.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Items Section -->
    <section class="popular-items">
        <div class="container">
            <h2 class="section-title">Popular Items</h2>
            <div class="items-grid">
                @forelse($popularMenus as $menu)
                    <div class="item-card">
                        <div class="item-image">{{ $menu->category?->name[0] ?? 'C' }}</div>
                        <h3>{{ $menu->name }}</h3>
                        <p>{{ $menu->description ?? 'Nikmati menu favorit pelanggan CafeIn.' }}</p>
                        <div class="item-footer">
                            <span class="price">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                            <button class="btn-add" data-menu-id="{{ $menu->id }}" {{ $menu->stock < 1 ? 'disabled' : '' }}>
                                {{ $menu->stock < 1 ? 'Out of Stock' : 'Add to Cart' }}
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="empty-message">Menu belum tersedia. Segera kembali lagi!</p>
                @endforelse
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Ready to Order?</h2>
            <p>Browse our full menu and place your order today!</p>
            <a href="{{ route('menus') }}" class="btn btn-light">View Full Menu</a>
        </div>
    </section>
@endsection
