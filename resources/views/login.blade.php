@extends('layouts.app')

@section('title', 'Login - CafeIn')

@section('content')
    <div class="auth-page">
        <div class="container auth-container">
            <div class="auth-card">
                <h1 class="auth-title">Masuk ke CafeIn</h1>
                <p class="auth-subtitle">Nikmati pengalaman terbaik sesuai peran Anda.</p>

                @if ($errors->any())
                    <div class="auth-alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="auth-form">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group-inline">
                        <label class="checkbox">
                            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                            <span>Ingat saya</span>
                        </label>
                        <a href="#" class="link-muted">Lupa password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary auth-submit">Login</button>
                </form>

                <p class="auth-switch">Belum memiliki akun?
                    <a href="{{ route('register') }}">Daftar sebagai customer</a>
                </p>
            </div>
        </div>
    </div>
@endsection
