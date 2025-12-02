@extends('layouts.app')

@section('title', 'Kelola Menu - Admin')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>Kelola Menu</h1>
            <p>Daftar menu dengan pencarian dan filter kategori.</p>
        </div>
    </div>

    <section class="dashboard-section">
        <div class="container">
            @if (session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="auth-alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="search-card">
                <div class="search-card-header">
                    <div>
                        <div class="search-card-title">Filter Menu</div>
                        <div class="search-card-subtitle">Cari menu dan batasi berdasarkan kategori.</div>
                    </div>
                    <a href="{{ route('admin.menus.create') }}" class="btn-primary">Tambah Menu</a>
                </div>

                <form id="menu-filter-form" method="GET" action="{{ route('admin.menus.index') }}">
                    <div class="search-row">
                        <div class="form-group search-main">
                            <label for="q">Cari</label>
                            <div class="search-input-wrap">
                                <span class="search-input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="6"></circle>
                                        <line x1="16" y1="16" x2="21" y2="21"></line>
                                    </svg>
                                </span>
                                <input type="text" id="q" name="q" value="{{ $filter['q'] ?? '' }}" placeholder="Nama / Deskripsi">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="category">Kategori</label>
                            <select id="category" name="category">
                                <option value="">Semua</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(($filter['category'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-secondary">Filter</button>
                    </div>
                </form>
            </div>

            <div class="table-wrapper">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($menus as $menu)
                            <tr>
                                <td>
                                    @if ($menu->image)
                                        <img src="{{ asset('storage/'.$menu->image) }}" alt="{{ $menu->name }}" class="thumb">
                                    @else
                                        <div class="thumb fallback">{{ strtoupper(substr($menu->name, 0, 1)) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $menu->name }}</strong>
                                    <div class="stat-helper">{{ Str::limit($menu->description, 60) }}</div>
                                </td>
                                <td>{{ $menu->category?->name ?? '-' }}</td>
                                <td>Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</td>
                                <td>{{ $menu->stock }}</td>
                                <td>
                                    @if ($menu->is_active)
                                        <span class="status-pill status-success">Aktif</span>
                                    @else
                                        <span class="status-pill status-cancelled">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.menus.edit', $menu) }}" class="btn-secondary">Edit</a>
                                    <form action="{{ route('admin.menus.toggle', $menu) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Ubah status aktif menu ini?');">
                                        @csrf
                                        <button type="submit" class="btn-secondary">{{ $menu->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                    <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Hapus menu ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">Belum ada menu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $menus->links('components.pagination') }}
            </div>
        </div>
    </section>

    <script>
        (function(){
            const form = document.getElementById('menu-filter-form');
            const q = document.getElementById('q');
            let t;
            if (form && q) {
                q.addEventListener('input', function(){
                    clearTimeout(t);
                    t = setTimeout(()=>form.submit(), 450);
                });
            }
        })();
    </script>
@endsection
