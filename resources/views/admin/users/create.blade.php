@extends('layouts.app')

@section('title', 'Create User - Admin')

@section('content')
    <section class="page-hero compact">
        <div class="page-hero-bg"></div>
        <div class="container">
            <div class="page-hero-content">
                <span class="page-badge">Admin · Pengguna</span>
                <h1>Tambah Pengguna</h1>
                <p>Buat akun baru dan pilih perannya.</p>
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
                <form method="POST" action="{{ route('admin.users.store') }}" class="auth-form">
                    @csrf

                    <div class="form-group">
                        <label for="name">Nama</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="opsional">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="role_id">Role</label>
                        <select name="role_id" id="role_id" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="page-actions">
                        <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
                        <button type="submit" class="btn-primary">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
