@extends('layouts.app')

@section('title', 'Menu - CafeIn')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>Our Menu</h1>
            <p>Discover our delicious selection</p>
            @if (auth()->check() && auth()->user()->role?->name === 'Admin')
                <a href="{{ route('admin.menus.create') }}" class="btn-primary">Tambah Menu</a>
            @endif
            <form method="GET" action="{{ route('menus') }}" class="auth-form" style="margin-top:12px; display:flex; gap:8px; align-items:end; flex-wrap:wrap;" id="menu-search-form">
                <div class="form-group" style="flex:1;">
                    <label for="q">Search Menu</label>
                    <input type="text" id="q" name="q" value="{{ $searchTerm ?? '' }}" placeholder="Cari nama atau deskripsi menu" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="category">Kategori</label>
                    <select id="category" name="category">
                        <option value="">Semua</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (string)($selectedCategory ?? '') === (string)$category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="min_price">Min Harga</label>
                    <input type="number" id="min_price" name="min_price" min="0" step="100" value="{{ $minPrice ?? '' }}">
                </div>
                <div class="form-group">
                    <label for="max_price">Max Harga</label>
                    <input type="number" id="max_price" name="max_price" min="0" step="100" value="{{ $maxPrice ?? '' }}">
                </div>
                <button type="submit" class="btn-secondary">Search</button>
                @if (!empty($searchTerm))
                    <a href="{{ route('menus') }}" class="btn-secondary">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <section class="menu-section">
        <div class="container">
            @php($isAdmin = auth()->check() && auth()->user()->role?->name === 'Admin')
            <div id="live-search-results" style="display:none;">
                <h2 class="category-title">Search Results</h2>
                <div class="menu-grid" id="live-results-grid"></div>
                <p class="empty-message" id="live-results-empty" style="display:none;">Tidak ditemukan hasil untuk pencarian ini.</p>
            </div>
            @if (!empty($searchTerm))
                <div class="menu-results">
                    <h2 class="category-title">Search Results for "{{ $searchTerm }}"</h2>
                    <div class="menu-grid">
                        @forelse($results as $menu)
                            @php($slug = \Illuminate\Support\Str::slug($menu->category?->name ?? 'other'))
                            <div class="menu-item" data-category="{{ $slug }}"
                                 data-menu-id="{{ $menu->id }}"
                                 data-menu-name="{{ $menu->name }}"
                                 data-menu-desc="{{ $menu->description ?? 'Menu favorit kami.' }}"
                                 data-menu-price="{{ (float) $menu->price }}"
                                 data-menu-image="{{ $menu->image ? asset('storage/'.$menu->image) : '' }}">
                                <div class="menu-item-image">
                                    @if ($menu->image)
                                        <img src="{{ asset('storage/'.$menu->image) }}" alt="{{ $menu->name }}" loading="lazy">
                                    @else
                                        <span class="avatar-fallback">{{ strtoupper(substr($menu->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="menu-item-content" @if(!$isAdmin) role="button" tabindex="0" aria-label="Detail {{ $menu->name }}" @endif>
                                    <h3>{{ $menu->name }}</h3>
                                    <p>{{ $menu->description ?? 'Menu favorit kami.' }}</p>
                                    <div class="menu-item-footer">
                                        <span class="price">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                                        @if ($isAdmin)
                                            <div class="page-actions">
                                                <a href="{{ route('admin.menus.edit', $menu) }}" class="btn-secondary">Edit</a>
                                                <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" onsubmit="return confirm('Hapus menu ini?');" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        @else
                                            <button class="btn-add" data-menu-id="{{ $menu->id }}" {{ $menu->stock < 1 ? 'disabled' : '' }}>
                                                {{ $menu->stock < 1 ? 'Out of Stock' : 'Add to Cart' }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="empty-message">Tidak ditemukan hasil untuk pencarian ini.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            <!-- Menu Filter -->
            @if (empty($searchTerm))
                <div class="menu-filter">
                    <button class="filter-btn active" data-category="all">All</button>
                    @foreach ($categories as $category)
                        @php($slug = \Illuminate\Support\Str::slug($category->name))
                        <button class="filter-btn" data-category="{{ $slug }}">{{ $category->name }}</button>
                    @endforeach
                </div>
            @endif

            @if (empty($searchTerm))
            @forelse($categories as $category)
                @php($slug = \Illuminate\Support\Str::slug($category->name))
                <div class="menu-category" id="{{ $slug }}">
                    <h2 class="category-title">{{ $category->name }}</h2>
                    <div class="menu-grid">
                        @forelse($category->menus as $menu)
                            <div class="menu-item" data-category="{{ $slug }}"
                                 data-menu-id="{{ $menu->id }}"
                                 data-menu-name="{{ $menu->name }}"
                                 data-menu-desc="{{ $menu->description ?? 'Menu favorit di kategori ini.' }}"
                                 data-menu-price="{{ (float) $menu->price }}"
                                 data-menu-image="{{ $menu->image ? asset('storage/'.$menu->image) : '' }}"
                                 data-menu-stock="{{ $menu->stock }}"
                                 data-menu-category="{{ $menu->category?->name }}"
                                 data-menu-meta-prep="{{ $menu->metadata['prep_time'] ?? '' }}"
                                 data-menu-meta-size="{{ $menu->metadata['serving_size'] ?? '' }}"
                                 data-menu-meta-cal="{{ $menu->metadata['calories'] ?? '' }}">
                                <div class="menu-item-image">
                                    @if ($menu->image)
                                        <img src="{{ asset('storage/'.$menu->image) }}" alt="{{ $menu->name }}" loading="lazy">
                                    @else
                                        <span class="avatar-fallback">{{ strtoupper(substr($menu->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="menu-item-content" @if(!$isAdmin) role="button" tabindex="0" aria-label="Detail {{ $menu->name }}" @endif>
                                    <h3>{{ $menu->name }}</h3>
                                    <p>{{ $menu->description ?? 'Menu favorit di kategori ini.' }}</p>
                                    <div class="menu-item-footer">
                                        <span class="price">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                                        @if ($isAdmin)
                                            <div class="page-actions">
                                                <a href="{{ route('admin.menus.edit', $menu) }}" class="btn-secondary">Edit</a>
                                                <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" onsubmit="return confirm('Hapus menu ini?');" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        @else
                                            <button class="btn-add" data-menu-id="{{ $menu->id }}" {{ $menu->stock < 1 ? 'disabled' : '' }}>
                                                {{ $menu->stock < 1 ? 'Out of Stock' : 'Add to Cart' }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="empty-message">Belum ada menu pada kategori ini.</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <p class="empty-message">Kategori menu belum tersedia.</p>
            @endforelse
            @endif
        </div>
    </section>
    <!-- Menu Detail Modal -->
    <div id="menu-detail-modal" class="modal" aria-hidden="true" aria-labelledby="menu-detail-title" role="dialog">
        <div class="modal-backdrop" data-close-modal></div>
        <div class="modal-dialog" role="document">
            <button class="modal-close" type="button" aria-label="Tutup" data-close-modal>&times;</button>
            <div class="modal-body">
                <div class="modal-image"><img alt="" id="md-image"></div>
                <div class="modal-content">
                    <h3 id="menu-detail-title"></h3>
                    <p class="modal-desc" id="md-desc"></p>
                    <div class="modal-meta">
                        <div><strong>Kategori:</strong> <span id="md-category">-</span></div>
                        <div><strong>Stok:</strong> <span id="md-stock">-</span></div>
                        <div><strong>Waktu Saji:</strong> <span id="md-prep">-</span></div>
                        <div><strong>Porsi:</strong> <span id="md-size">-</span></div>
                        <div><strong>Kalori:</strong> <span id="md-cal">-</span></div>
                    </div>
                    <div class="modal-footer">
                        <span class="price" id="md-price"></span>
                        <div class="quantity-control">
                            <button type="button" class="qty-btn" id="md-qty-dec" aria-label="Kurangi">−</button>
                            <input type="number" id="md-qty-input" class="quantity-input" value="1" min="1">
                            <button type="button" class="qty-btn" id="md-qty-inc" aria-label="Tambah">+</button>
                        </div>
                        <button class="btn-primary btn-add" id="md-add" data-menu-id="">Tambah ke Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
