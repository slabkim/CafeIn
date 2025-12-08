@extends('layouts.app')

@section('title', 'Home - CafeIn')

@section('content')
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-bg"></div>
        <div class="container hero-container">
            <div class="hero-content">
                <span class="hero-badge">Premium Coffee Experience</span>
                <h1 class="hero-title">Rasakan Kopi <span class="text-accent">Terbaik</span> di CafeIn</h1>
                <p class="hero-subtitle">Nikmati secangkir kopi berkualitas tinggi dengan suasana yang nyaman. Dari biji
                    pilihan hingga penyajian sempurna.</p>
                <div class="hero-actions">
                    <a href="{{ route('menus') }}" class="btn btn-primary btn-lg">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M4 8h12v5a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8z"></path>
                            <path d="M16 10h2a3 3 0 0 1 0 6h-2"></path>
                        </svg>
                        Lihat Menu
                    </a>
                    <a href="#features" class="btn btn-outline btn-lg">Pelajari Lebih</a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number">50+</span>
                        <span class="stat-label">Menu Pilihan</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-number">10K+</span>
                        <span class="stat-label">Pelanggan</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-number">4.9</span>
                        <span class="stat-label">Rating</span>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-card">
                    <div class="hero-card-image">
                        <img src="{{ asset('images/Vanilla Caramel Lattes Recipe.png') }}" alt="Coffee">
                    </div>

                    <div class="floating-card card-1">
                        <div class="floating-icon">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <span class="floating-title">Best Seller</span>
                            <span class="floating-subtitle">Caramel Latte</span>
                        </div>
                    </div>
                    <div class="floating-card card-2">
                        <div class="floating-icon green">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div>
                            <span class="floating-title">Fresh Daily</span>
                            <span class="floating-subtitle">100% Arabica</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Keunggulan Kami</span>
                <h2 class="section-title">Mengapa Memilih <span class="text-accent">CafeIn</span>?</h2>
                <p class="section-subtitle">Kami berkomitmen memberikan pengalaman kopi terbaik untuk Anda</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 8h12v5a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8z"></path>
                            <path d="M16 10h2a3 3 0 0 1 0 6h-2"></path>
                            <path d="M6 19h10"></path>
                        </svg>
                    </div>
                    <h3>Kopi Premium</h3>
                    <p>Biji kopi pilihan dari petani terbaik Indonesia, dipanggang dengan sempurna.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <h3>Cepat & Tepat</h3>
                    <p>Pesanan diproses dengan cepat tanpa mengorbankan kualitas rasa.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 11l9-7 9 7v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </div>
                    <h3>Suasana Nyaman</h3>
                    <p>Tempat ideal untuk bekerja, belajar, atau sekadar bersantai.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13" rx="2" ry="2"></rect>
                            <path d="M16 8h2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2"></path>
                            <line x1="5" y1="21" x2="19" y2="21"></line>
                            <line x1="12" y1="16" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h3>Pesan Online</h3>
                    <p>Pesan dari mana saja dengan mudah melalui platform kami.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Items Section -->
    <section class="popular-section">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Menu Favorit</span>
                <h2 class="section-title">Menu <span class="text-accent">Terlaris</span></h2>
                <p class="section-subtitle">Pilihan favorit pelanggan kami yang wajib Anda coba</p>
            </div>
            <div class="popular-grid">
                @forelse($popularMenus as $index => $menu)
                    <div class="popular-card {{ $index === 0 ? 'featured' : '' }}">
                        @if ($index === 0)
                            <span class="popular-badge">Best Seller</span>
                        @endif
                        <div class="popular-image">
                            @if ($menu->image)
                                <img src="{{ Storage::url($menu->image) }}" alt="{{ $menu->name }}">
                            @else
                                <img src="/placeholder.svg?height=200&width=300" alt="{{ $menu->name }}">
                            @endif
                        </div>
                        <div class="popular-content">
                            <span class="popular-category">{{ $menu->category?->name ?? 'Coffee' }}</span>
                            <h3>{{ $menu->name }}</h3>
                            <p>{{ Str::limit($menu->description ?? 'Nikmati menu favorit pelanggan CafeIn.', 60) }}</p>
                            <div class="popular-footer">
                                <span class="popular-price">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                                <button class="btn-cart btn-add" data-menu-id="{{ $menu->id }}"
                                    {{ $menu->stock < 1 ? 'disabled' : '' }}>
                                    @if ($menu->stock < 1)
                                        <span>Habis</span>
                                    @else
                                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6">
                                            </path>
                                        </svg>
                                        <span>Tambah</span>
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor"
                            stroke-width="1.5">
                            <path d="M4 8h12v5a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8z"></path>
                            <path d="M16 10h2a3 3 0 0 1 0 6h-2"></path>
                        </svg>
                        <p>Menu belum tersedia. Segera kembali lagi!</p>
                    </div>
                @endforelse
            </div>
            <div class="section-action">
                <a href="{{ route('menus') }}" class="btn btn-primary btn-lg">
                    Lihat Semua Menu
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-card">
                <div class="cta-content">
                    <h2>Siap Memesan?</h2>
                    <p>Jelajahi menu lengkap kami dan temukan kopi favorit Anda hari ini!</p>
                    <div class="cta-actions">
                        <a href="{{ route('menus') }}" class="btn btn-light btn-lg">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M4 8h12v5a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8z"></path>
                                <path d="M16 10h2a3 3 0 0 1 0 6h-2"></path>
                            </svg>
                            Pesan Sekarang
                        </a>
                    </div>
                </div>
                <div class="cta-decoration">
                    <div class="cta-circle"></div>
                    <div class="cta-circle"></div>
                </div>
            </div>
        </div>
    </section>
@endsection
