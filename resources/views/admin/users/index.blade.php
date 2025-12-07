@extends('layouts.app')

@section('title', 'User Management - Admin')

@section('content')
    <section class="page-hero compact">
        <div class="page-hero-bg"></div>
        <div class="container">
            <div class="page-hero-content">
                <span class="page-badge">Admin · Pengguna</span>
                <h1>User Management</h1>
                <p>Kelola pengguna dan perannya.</p>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="container">
            @if (session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="auth-alert">{{ session('error') }}</div>
            @endif

            <div class="search-card">
                <div class="search-card-header">
                    <div>
                        <div class="search-card-title">Filter Pengguna</div>
                        <div class="search-card-subtitle">Cari berdasarkan nama, email, dan role.</div>
                    </div>
                    <a href="{{ route('admin.users.create') }}" class="btn-primary">Tambah User</a>
                </div>

                <form id="user-filter-form" method="GET" action="{{ route('admin.users.index') }}">
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
                                <input type="text" id="q" name="q" value="{{ $filter['q'] ?? '' }}" placeholder="Nama atau Email">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select name="role" id="role">
                                <option value="">Semua</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected(($filter['role'] ?? '') == $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-secondary">Filter</button>
                    </div>
                </form>
            </div>

            <form method="POST" action="{{ route('admin.users.bulk') }}">
                @csrf
                <div class="table-wrapper">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th style="width:36px;"><input type="checkbox" id="select-all"></th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $user->id }}" class="row-check"></td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role?->name ?? '-' }}</td>
                                <td>
                                    <a class="btn-secondary" href="{{ route('admin.users.edit', $user) }}">Ubah Role</a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline-block;"
                                        onsubmit="return confirm('Hapus pengguna ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" {{ auth()->id() === $user->id ? 'disabled' : '' }}>Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">Belum ada pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>

                <div class="page-actions mt-3">
                    <label>Aksi Terpilih:</label>
                    <select name="action" id="bulk-action" required>
                        <option value="">Pilih Aksi</option>
                        <option value="delete">Hapus</option>
                        <option value="change_role">Ubah Role</option>
                    </select>
                    <select name="role_id" id="bulk-role" disabled>
                        <option value="">Pilih Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-primary">Terapkan</button>
                </div>
            </form>

            <div class="pagination-wrapper">
                {{ $users->links('components.pagination') }}
            </div>
        </div>
    </section>

    <script>
        (function() {
            const selectAll = document.getElementById('select-all');
            const rowChecks = document.querySelectorAll('.row-check');
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    rowChecks.forEach(cb => cb.checked = selectAll.checked);
                });
            }
            const actionSel = document.getElementById('bulk-action');
            const roleSel = document.getElementById('bulk-role');
            if (actionSel && roleSel) {
                actionSel.addEventListener('change', function() {
                    roleSel.disabled = this.value !== 'change_role';
                    if (roleSel.disabled) roleSel.value = '';
                });
            }

            // Debounce search submit on typing
            const filterForm = document.getElementById('user-filter-form');
            const qInput = document.getElementById('q');
            let t;
            if (filterForm && qInput) {
                qInput.addEventListener('input', function() {
                    clearTimeout(t);
                    t = setTimeout(() => filterForm.submit(), 450);
                });
            }
        })();
    </script>
@endsection
