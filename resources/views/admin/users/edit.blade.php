@extends('layouts.app')

@section('title', 'Edit User Role - Admin')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>Edit Role Pengguna</h1>
            <p>Ubah peran untuk akses yang tepat.</p>
        </div>
    </div>

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
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="auth-form">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" value="{{ $user->name }}" disabled>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="{{ $user->email }}" disabled>
                    </div>

                    <div class="form-group">
                        <label for="role_id">Role</label>
                        <select name="role_id" id="role_id" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr>
                    <div class="form-group">
                        <label for="password">Reset Password (opsional)</label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" style="flex:1;">
                            <button type="button" id="btn-generate-pass" class="btn-secondary">Generate</button>
                        </div>
                        <small class="help">Biarkan kosong jika tidak ingin mengubah password. Klik "Generate" untuk membuat sandi kuat dan menyalinnya ke clipboard.</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation">
                    </div>

                    <div class="page-actions">
                        <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
                        <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>

            <div class="form-card" style="margin-top: 16px;">
                <form method="POST" action="{{ route('admin.users.reset', $user) }}">
                    @csrf
                    <div class="page-actions" style="justify-content: space-between; align-items:center;">
                        <div>
                            <strong>Kirim tautan reset password ke email pengguna ini</strong>
                            <p class="auth-subtitle">Pastikan konfigurasi email pada aplikasi sudah benar.</p>
                        </div>
                        <button type="submit" class="btn-secondary">Kirim Reset Link</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    (function(){
        function generatePassword(length = 14) {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*()-_=+';
            const picks = [];
            for (let i = 0; i < length; i++) {
                const idx = Math.floor(Math.random() * chars.length);
                picks.push(chars[idx]);
            }
            return picks.join('');
        }

        const genBtn = document.getElementById('btn-generate-pass');
        const pass = document.getElementById('password');
        const pass2 = document.getElementById('password_confirmation');
        if (genBtn && pass && pass2) {
            genBtn.addEventListener('click', async function(){
                const pwd = generatePassword(14);
                pass.value = pwd;
                pass2.value = pwd;
                try {
                    if (navigator.clipboard && window.isSecureContext !== false) {
                        await navigator.clipboard.writeText(pwd);
                        if (typeof showNotification === 'function') {
                            showNotification('Password digenerate dan disalin ke clipboard.', 'success');
                        } else {
                            alert('Password digenerate dan disalin ke clipboard.');
                        }
                    } else {
                        if (typeof showNotification === 'function') {
                            showNotification('Password digenerate. Salin manual dari kolom.', 'success');
                        } else {
                            alert('Password digenerate. Salin manual dari kolom.');
                        }
                    }
                } catch(e) {
                    if (typeof showNotification === 'function') {
                        showNotification('Password digenerate. Gagal menyalin otomatis.', 'error');
                    } else {
                        alert('Password digenerate. Gagal menyalin otomatis.');
                    }
                }
            });
        }
    })();
    </script>
@endpush
