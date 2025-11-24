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

            <div class="page-actions actions-row mb-3">
                <a href="{{ route('admin.menus.create') }}" class="btn-primary">Tambah Menu</a>

                <form id="menu-filter-form" method="GET" action="{{ route('admin.menus.index') }}" class="page-actions actions-row">
                    <div class="form-group">
                        <label for="q">Cari</label>
                        <input type="text" id="q" name="q" value="{{ $filter['q'] ?? '' }}" placeholder="Nama / Deskripsi">
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
