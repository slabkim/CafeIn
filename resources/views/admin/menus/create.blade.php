@extends('layouts.app')

@section('title', 'Tambah Menu - CafeIn')

@section('content')
    <section class="page-hero compact">
        <div class="page-hero-bg"></div>
        <div class="container">
            <div class="page-hero-content">
                <span class="page-badge">Admin · Menu</span>
                <h1>Tambah Menu Baru</h1>
                <p>Tambahkan menu baru ke dalam sistem CafeIn.</p>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="container">
            @if ($errors->any())
                <div class="auth-alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-card">
                <form action="{{ route('admin.menus.store') }}" method="POST" enctype="multipart/form-data" class="auth-form">
                    @csrf

                    <div class="form-group">
                        <label for="name">Nama Menu</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="image">Gambar (opsional)</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        @error('image')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="price">Harga</label>
                        <input type="number" id="price" name="price" value="{{ old('price') }}" min="0" step="0.01" required>
                        @error('price')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="stock">Stok</label>
                        <input type="number" id="stock" name="stock" value="{{ old('stock') }}" min="0" required>
                        @error('stock')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="category_id">Kategori</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('menus') }}" class="btn-secondary">Batal</a>
                        <button type="submit" class="btn-primary">Tambah Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
