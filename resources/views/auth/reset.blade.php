@extends('layouts.app')

@section('title', 'Reset Password - CafeIn')

@section('content')
    <div class="auth-page">
        <div class="container auth-container">
            <div class="auth-card">
                <h1 class="auth-title">Reset Password</h1>
                <p class="auth-subtitle">Masukkan password baru Anda.</p>

                @if ($errors->any())
                    <div class="auth-alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="auth-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-primary auth-submit">Perbarui Password</button>
                </form>
            </div>
        </div>
    </div>
@endsection

