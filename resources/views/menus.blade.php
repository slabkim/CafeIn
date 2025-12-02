@extends('layouts.app')

@section('title', 'Menu - CafeIn')

@section('content')
    <!-- Page Header -->
    <section class="page-hero">
        <div class="page-hero-bg"></div>
        <div class="container">
            <div class="page-hero-content">
                <span class="page-badge">Explore</span>
                <h1>Menu <span class="text-accent">Kami</span></h1>
                <p>Temukan berbagai pilihan kopi dan makanan lezat untuk menemani harimu</p>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <div class="search-card-modern">
                <div class="search-header">
                    <div class="search-icon-wrap">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <div>
                        <h3>Cari Menu Favorit</h3>
                        <p>Filter berdasarkan nama, kategori, dan harga</p>
                    </div>
                    @if (auth()->check() && auth()->user()->role?->name === 'Admin')
                        <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Tambah Menu
                        </a>
                    @endif
                </div>
                <form method="GET" action="{{ route('menus') }}" class="search-form" id="menu-search-form">
                    <div class="search-grid">
                        <div class="search-input-group main">
                            <label for="q">Cari</label>
                            <div class="input-icon-wrap">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <input type="text" id="q" name="q" value="{{ $searchTerm ?? '' }}" placeholder="Nama atau deskripsi menu...">
                            </div>
                        </div>
                        <div class="search-input-group">
                            <label for="category">Kategori</label>
                            <select id="category" name="category">
                                <option value="">Semua</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ (string)($selectedCategory ?? '') === (string)$category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="search-input-group">
                            <label for="min_price">Min Harga</label>
                            <input type="number" id="min_price" name="min_price" min="0" step="1000" value="{{ $minPrice ?? '' }}" placeholder="0">
                        </div>
                        <div class="search-input-group">
                            <label for="max_price">Max Harga</label>
                            <input type="number" id="max_price" name="max_price" min="0" step="1000" value="{{ $maxPrice ?? '' }}" placeholder="100000">
                        </div>
                        <div class="search-actions">
                            <button type="submit" class="btn btn-primary">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                Cari
                            </button>
                            @if (!empty($searchTerm) || !empty($selectedCategory) || !empty($minPrice) || !empty($maxPrice))
                                <a href="{{ route('menus') }}" class="btn btn-ghost">Reset</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Menu Section -->
    <section class="menu-section-modern">
        <div class="container">
            @php($isAdmin = auth()->check() && auth()->user()->role?->name === 'Admin')

            <!-- Search Results -->
            @if (!empty($searchTerm))
                <div class="menu-results">
                    <div class="results-header">
                        <h2>Hasil Pencarian untuk "<span class="text-accent">{{ $searchTerm }}</span>"</h2>
                        <span class="results-count">{{ count($results) }} menu ditemukan</span>
                    </div>
                    <div class="menu-grid-modern">
                        @forelse($results as $menu)
                            @include('partials.menu-card', ['menu' => $menu, 'isAdmin' => $isAdmin])
                        @empty
                            <div class="empty-state full-width">
                                <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <p>Tidak ditemukan hasil untuk "{{ $searchTerm }}"</p>
                                <a href="{{ route('menus') }}" class="btn btn-outline">Lihat Semua Menu</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            @else
                <!-- Category Filter -->
                <div class="filter-tabs menu-filter">
                    <button class="filter-tab filter-btn active" data-category="all">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        Semua
                    </button>
                    @foreach ($categories as $category)
                        @php($slug = \Illuminate\Support\Str::slug($category->name))
                        <button class="filter-tab filter-btn" data-category="{{ $slug }}">{{ $category->name }}</button>
                    @endforeach
                </div>

                <!-- Menu Categories -->
                @forelse($categories as $category)
                    @php($slug = \Illuminate\Support\Str::slug($category->name))
                    <div class="menu-category-section menu-category" id="{{ $slug }}" data-category-section="{{ $slug }}">
                        <div class="category-header">
                            <h2>{{ $category->name }}</h2>
                            <span class="category-count">{{ $category->menus->count() }} item</span>
                        </div>
                        <div class="menu-grid-modern">
                            @forelse($category->menus as $menu)
                                @include('partials.menu-card', ['menu' => $menu, 'isAdmin' => $isAdmin, 'slug' => $slug])
                            @empty
                                <div class="empty-state">
                                    <p>Belum ada menu pada kategori ini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 8h12v5a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8z"></path>
                            <path d="M16 10h2a3 3 0 0 1 0 6h-2"></path>
                        </svg>
                        <p>Kategori menu belum tersedia.</p>
                    </div>
                @endforelse
            @endif
        </div>
    </section>

    <!-- Menu Detail Modal -->
    <div id="menu-detail-modal" class="modal-overlay" aria-hidden="true">
        <div class="modal-backdrop" data-close-modal></div>
        <div class="modal-container">
            <button class="modal-close" type="button" aria-label="Tutup" data-close-modal>
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <div class="modal-grid">
                <div class="modal-image-wrap">
                    <img alt="" id="md-image">
                </div>
                <div class="modal-details">
                    <span class="modal-category" id="md-category">Coffee</span>
                    <h3 id="menu-detail-title"></h3>
                    <p class="modal-desc" id="md-desc"></p>
                    <div class="modal-info-grid">
                        <div class="info-item">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="3" y1="9" x2="21" y2="9"></line>
                            </svg>
                            <span>Stok: <strong id="md-stock">-</strong></span>
                        </div>
                        <div class="info-item">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span>Waktu: <strong id="md-prep">-</strong></span>
                        </div>
                        <div class="info-item">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                                <line x1="6" y1="1" x2="6" y2="4"></line>
                                <line x1="10" y1="1" x2="10" y2="4"></line>
                                <line x1="14" y1="1" x2="14" y2="4"></line>
                            </svg>
                            <span>Porsi: <strong id="md-size">-</strong></span>
                        </div>
                        <div class="info-item">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                            </svg>
                            <span>Kalori: <strong id="md-cal">-</strong></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="modal-price" id="md-price">Rp 0</div>
                        <div class="modal-actions">
                            <div class="qty-control">
                                <button type="button" class="qty-btn" id="md-qty-dec" aria-label="Kurangi">-</button>
                                <input type="number" id="md-qty-input" class="qty-input" value="1" min="1">
                                <button type="button" class="qty-btn" id="md-qty-inc" aria-label="Tambah">+</button>
                            </div>
                            <button class="btn btn-primary btn-add-cart btn-add" id="md-add" data-menu-id="">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                Tambah ke Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

