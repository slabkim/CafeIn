@extends('layouts.app')

@section('title', 'Dashboard Admin - CafeIn')

@section('content')
    <section class="page-hero compact">
        <div class="page-hero-bg"></div>
        <div class="container">
            <div class="page-hero-content">
                <span class="page-badge">Admin</span>
                <h1>Dashboard Admin</h1>
                <p>Ikhtisar operasional CafeIn dengan tampilan yang selaras dengan UI pengguna.</p>
                <div class="page-actions">
                    <a href="{{ route('orders') }}" class="btn btn-light btn-sm">Lihat Pesanan</a>
                    <a href="{{ route('payments') }}" class="btn btn-primary btn-sm">Pantau Pembayaran</a>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-grid">
                <div class="stat-card">
                    <span class="stat-label">Total Revenue</span>
                    <span class="stat-value">Rp {{ number_format((float) $totalRevenue, 0, ',', '.') }}</span>
                    <span class="stat-helper">Akumulasi pembayaran sukses</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Orders Today</span>
                    <span class="stat-value">{{ $ordersToday }}</span>
                    <span class="stat-helper">Pesanan masuk hari ini</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Active Orders</span>
                    <span class="stat-value">{{ $pendingOrders }}</span>
                    <span class="stat-helper">Pending & processing</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">New Customers (7d)</span>
                    <span class="stat-value">{{ $newCustomers }}</span>
                    <span class="stat-helper">Registrasi baru minggu ini</span>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="panel-header">
                    <div>
                        <h2>Pusat Kendali</h2>
                        <span>Akses cepat dengan gaya kartu ala user</span>
                    </div>
                    <div class="page-actions">
                        <a href="{{ route('admin.menus.create') }}" class="btn btn-primary btn-sm">Tambah Menu</a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">Kelola Pengguna</a>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <a class="stat-card" href="{{ route('admin.users.index') }}">
                        <span class="stat-label">User Management</span>
                        <span class="stat-helper">Kelola akun & role</span>
                        <div class="page-actions" style="margin-top:8px;">
                            <span class="btn-secondary">Buka</span>
                        </div>
                    </a>

                    <div class="stat-card">
                        <span class="stat-label">Menu Management</span>
                        <span class="stat-helper">Tambah & atur menu</span>
                        <div class="page-actions">
                            <a href="{{ route('admin.menus.create') }}" class="btn-primary">Tambah Menu</a>
                            <a href="{{ route('admin.menus.index') }}" class="btn-secondary">Kelola Menu</a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <span class="stat-label">Order Management</span>
                        <span class="stat-helper">Pantau pesanan pelanggan</span>
                        <div class="page-actions">
                            <a href="{{ route('orders') }}" class="btn-secondary">Buka Orders</a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <span class="stat-label">Payments</span>
                        <span class="stat-helper">Verifikasi & review transaksi</span>
                        <div class="page-actions">
                            <a href="{{ route('payments') }}" class="btn-secondary">Buka Payments</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="panel-header">
                    <div>
                        <h2>Latest Orders</h2>
                        <span>Terhubung langsung dengan UI tracking pengguna</span>
                    </div>
                    <div class="page-actions">
                        <a href="{{ route('orders') }}" class="btn btn-light btn-sm">Lihat semua</a>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr onclick="window.location='{{ route('orders.track', $order) }}'" style="cursor:pointer;">
                                    <td>#{{ $order->order_number }}</td>
                                    <td>{{ $order->user?->name ?? 'Guest' }}</td>
                                    <td><span class="status-pill status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                                    <td>Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</td>
                                    <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">Belum ada order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
