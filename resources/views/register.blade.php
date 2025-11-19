@extends('layouts.app')

@section('title', 'Register - CafeIn')

@section('content')
    <div class="auth-page">
        <div class="container auth-container">
            <div class="auth-card">
                <h1 class="auth-title">Daftar sebagai Customer</h1>
                <p class="auth-subtitle">Buat akun untuk memesan menu dan menikmati promo.</p>

                @if ($errors->any())
                    <div class="auth-alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="auth-form">
                    @csrf

                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
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
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-primary auth-submit">Daftar</button>
                </form>

                <p class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk sekarang</a></p>
            </div>
        </div>
    </div>
@endsection
