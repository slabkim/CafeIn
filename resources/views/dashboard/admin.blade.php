@extends('layouts.app')

@section('title', 'Admin Console - CafeIn')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>Admin Console</h1>
            <p>Fokus pada pengelolaan pengguna, menu, dan pembayaran.</p>
        </div>
    </div>

    <section class="dashboard-section">
        <div class="container">
            <!-- Ringkas: Statistik Utama -->
            <div class="dashboard-grid">
                <div class="stat-card">
                    <span class="stat-label">Total Revenue</span>
                    <span class="stat-value">Rp {{ number_format((float) $totalRevenue, 0, ',', '.') }}</span>
                    <span class="stat-helper">Pembayaran sukses</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Orders Today</span>
                    <span class="stat-value">{{ $ordersToday }}</span>
                    <span class="stat-helper">Hari ini</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Active Orders</span>
                    <span class="stat-value">{{ $pendingOrders }}</span>
                    <span class="stat-helper">Pending / processing</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">New Customers (7d)</span>
                    <span class="stat-value">{{ $newCustomers }}</span>
                    <span class="stat-helper">Minggu ini</span>
                </div>
            </div>

            <!-- Hub Manajemen -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h2>Management Hub</h2>
                    <span>Akses cepat fitur administrasi</span>
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
                        <span class="stat-helper">Pantau semua order</span>
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

            <!-- Ringkasan Order Terbaru -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h2>Latest Orders</h2>
                    <span>5 terbaru</span>
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
                                <tr onclick="window.location='{{ route('orders') }}'" style="cursor:pointer;">
                                    <td>#{{ $order->order_number }}</td>
                                    <td>{{ $order->user?->name ?? 'Guest' }}</td>
                                    <td><span class="status-pill status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                                    <td>Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</td>
                                    <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">No orders yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
