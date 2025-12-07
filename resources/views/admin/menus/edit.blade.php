@extends('layouts.app')

@section('title', 'Edit Menu - CafeIn')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>Edit Menu</h1>
            <p>Perbarui informasi menu dan kelola galeri gambar.</p>
        </div>
    </div>

    <section class="form-section">
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

            <div class="form-card">
                <form action="{{ route('admin.menus.update', $menu) }}" method="POST" enctype="multipart/form-data" class="auth-form">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="name">Nama Menu</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $menu->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" rows="3">{{ old('description', $menu->description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Harga</label>
                        <input type="number" id="price" name="price" value="{{ old('price', $menu->price) }}" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="stock">Stok</label>
                        <input type="number" id="stock" name="stock" value="{{ old('stock', $menu->stock) }}" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Kategori</label>
                        <select id="category_id" name="category_id" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (old('category_id', $menu->category_id) == $category->id) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="image">Gambar Sampul</label>
                        @if ($menu->image)
                            <div class="mb-3">
                            @php($coverUrl = $menu->image ? Storage::url($menu->image) : '')
                            @if($coverUrl)
                                <img src="{{ $coverUrl }}" alt="{{ $menu->name }}" style="max-width: 280px; border-radius: 8px; display:block;">
                            @endif
                            </div>
                            <label class="checkbox">
                                <input type="checkbox" name="remove_image" value="1"> Hapus gambar sampul
                            </label>
                        @endif
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="images">Galeri Tambahan</label>
                        <input type="file" id="images" name="images[]" accept="image/*" multiple>
                        <small class="help">Anda dapat mengunggah beberapa gambar sekaligus.</small>
                    </div>

                    <div class="page-actions">
                        <a href="{{ route('menus') }}" class="btn-secondary">Kembali</a>
                        <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
                <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" class="page-actions" onsubmit="return confirm('Hapus menu ini? Semua gambar akan dihapus.');" style="margin-top: 12px;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Hapus Menu</button>
                </form>
            </div>

            @if ($menu->images->count())
                <div class="dashboard-panel">
                    <div class="panel-header">
                        <h2>Galeri</h2>
                        <span>{{ $menu->images->count() }} gambar</span>
                    </div>
                    <div class="dashboard-grid">
                        @foreach ($menu->images as $img)
                            <div class="form-card" style="text-align:center;">
                            <img src="{{ Storage::url($img->path) }}" alt="{{ $menu->name }}" style="width:100%; max-height:200px; object-fit:cover; border-radius:8px;">
                                <form action="{{ route('admin.menus.images.destroy', [$menu, $img]) }}" method="POST" class="mt-3" onsubmit="return confirm('Hapus gambar ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Hapus</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
