@extends('layouts.app')

@section('title', 'Login - CafeIn')

@section('content')
<div class="auth-wrapper">
    <div class="auth-left">
        <div class="auth-brand">
            <div class="brand-logo">
                <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                    <path d="M12 18C12 14.6863 14.6863 12 18 12H30C33.3137 12 36 14.6863 36 18V28C36 33.5228 31.5228 38 26 38H22C16.4772 38 12 33.5228 12 28V18Z" stroke="currentColor" stroke-width="2.5"/>
                    <path d="M36 20H38C40.2091 20 42 21.7909 42 24V24C42 26.2091 40.2091 28 38 28H36" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M18 8V12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M24 6V12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M30 8V12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
            <h1>CafeIn</h1>
            <p class="brand-tagline">Nikmati kopi terbaik untuk harimu</p>
        </div>
        <div class="auth-illustration">
            <div class="floating-beans">
                <span class="bean bean-1">☕</span>
                <span class="bean bean-2">🫘</span>
                <span class="bean bean-3">☕</span>
            </div>
        </div>
        <div class="auth-features">
            <div class="feature-item">
                <span class="feature-icon">✨</span>
                <span>Pesan menu favorit dengan mudah</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">🎁</span>
                <span>Dapatkan promo & rewards eksklusif</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">⚡</span>
                <span>Proses cepat tanpa antri</span>
            </div>
        </div>
    </div>
    
    <div class="auth-right">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Selamat Datang Kembali</h2>
                <p>Masuk ke akun CafeIn Anda</p>
            </div>

            @if ($errors->any())
                <div class="auth-alert auth-alert-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <div class="alert-content">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="auth-social">
                <a href="{{ route('auth.google.redirect') }}" class="btn-social btn-google">
                    <svg width="20" height="20" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span>Masuk dengan Google</span>
                </a>
            </div>

            <div class="auth-divider">
                <span>atau masuk dengan email</span>
            </div>

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M22 6L12 13L2 6"/>
                        </svg>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            placeholder="nama@email.com"
                            required 
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-group">
                    <div class="label-row">
                        <label for="password">Password</label>
                        <a href="#" class="forgot-link">Lupa password?</a>
                    </div>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="••••••••"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="eye-closed hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group-inline">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <span class="checkbox-label">Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    <span>Masuk</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </button>
            </form>

            <p class="auth-switch">
                Belum memiliki akun? 
                <a href="{{ route('register') }}">Daftar sekarang</a>
            </p>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.toggle-password');
    const eyeOpen = button.querySelector('.eye-open');
    const eyeClosed = button.querySelector('.eye-closed');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    }
}
</script>
@endsection
