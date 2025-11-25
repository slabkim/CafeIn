    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-logged-in" content="{{ auth()->check() ? 'true' : 'false' }}">
        <title>@yield('title', 'CafeIn - Your Cozy Coffee Corner')</title>
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
            rel="stylesheet">
    </head>

    <body>
        <!-- Navigation -->
        <nav class="navbar" aria-label="Main">
            <div class="container">
                <!-- KIRI: Brand -->
                <div class="nav-brand">
                    <h1 style="margin:0;">CafeIn</h1>
                    <span class="tagline">Your Cozy Coffee Corner</span>
                </div>

                <!-- Mobile Hamburger Toggle Button -->
                <button class="nav-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false"
                    aria-controls="nav-menu nav-actions">
                    <span class="bar" aria-hidden="true"></span>
                    <span class="bar" aria-hidden="true"></span>
                    <span class="bar" aria-hidden="true"></span>
                </button>

                <!-- TENGAH: Menu -->
                <ul id="nav-menu" class="nav-menu">
                    @php $isAdminRole = auth()->check() && auth()->user()->role?->name === 'Admin'; @endphp
                    @auth
                        @if (auth()->user()->role?->name === 'Admin')
                            <li><a href="{{ route('admin.dashboard') }}"
                                    class="{{ Route::is('admin.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                        @elseif (auth()->user()->role?->name === 'Kasir')
                            <li><a href="{{ route('kasir.dashboard') }}"
                                    class="{{ Route::is('kasir.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                        @endif
                    @endauth
                    @php $hideHome = auth()->check() && in_array(auth()->user()->role?->name, ['Admin', 'Kasir']); @endphp
                    @if (!$hideHome)
                        <li><a href="{{ url('/') }}" class="{{ Request::is('/') ? 'active' : '' }}">Home</a></li>
                    @endif
                    <li><a href="{{ url('/menus') }}" class="{{ Request::is('menus') ? 'active' : '' }}">Menu</a></li>
                    <li><a href="{{ url('/orders') }}" class="{{ Request::is('orders') ? 'active' : '' }}">Orders</a>
                    </li>
                    @unless ($isAdminRole)
                        <li><a href="{{ url('/payments') }}"
                                class="{{ Request::is('payments') ? 'active' : '' }}">Payments</a></li>
                    @endunless
                </ul>

                <!-- KANAN: Actions (cart + auth) -->
                <div id="nav-actions" class="nav-actions">
                    @auth
                        @php $roleName = auth()->user()->role?->name; @endphp
                        @if ($roleName !== 'Admin')
                            @php
                                $initialCartCount =
                                    \App\Models\Cart::firstWhere('user_id', auth()->id())
                                        ?->cartItems()
                                        ->sum('quantity') ?? 0;
                            @endphp
                            <a href="{{ route('cart') }}" class="nav-cart" aria-label="Cart">
                                <span class="cart-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h3l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L21 6H5"></path>
                                        <path d="M7 11h12"></path>
                                    </svg>
                                </span>
                                <span class="cart-count">{{ $initialCartCount }}</span>
                            </a>
                        @endif
                    @endauth

                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                    @endguest
                    @auth
                        <div class="nav-profile">
                            <span class="profile-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">
                                    <circle cx="12" cy="8" r="4"></circle>
                                    <path d="M4 20a8 8 0 0 1 16 0"></path>
                                </svg>
                            </span>
                            <div class="profile-info">
                                <span class="profile-name">{{ auth()->user()->name }}</span>
                                <span class="profile-role">{{ auth()->user()->role?->name ?? 'User' }}</span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                                @csrf
                                <button type="submit" class="btn-link">Logout</button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Small Inline Script to Toggle Mobile Menu -->
        <script>
            (function() {
                const nav = document.querySelector('.navbar');
                const toggle = document.querySelector('.nav-toggle');
                if (!nav || !toggle) return;
                toggle.addEventListener('click', () => {
                    const open = nav.classList.toggle('is-open');
                    toggle.setAttribute('aria-expanded', String(open));
                });
            })();
        </script>

        <!-- Main Content -->
        <main class="main-content">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>CafeIn</h3>
                    <p>Bringing you the finest coffee experience since 2024</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li><a href="{{ url('/menus') }}">Menu</a></li>
                        <li><a href="{{ url('/orders') }}">Orders</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Us</h4>
                    <p>Jl. Pangeran Antasari, Bandar Lampung</p>
                    <p>Telp: +62 812-3456-7890</p>
                    <p>Email: hello@cafein.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 CafeIn. All rights reserved.</p>
            </div>
        </footer>

        <script src="{{ asset('js/app.js') }}"></script>
        <script>
            function updateCartCount() {
                fetch('{{ route('cart.count') }}')
                    .then(response => response.json())
                    .then(data => {
                        const cartCountElements = document.querySelectorAll('.cart-count');
                        cartCountElements.forEach(el => {
                            el.textContent = data.count;
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching cart count:', error);
                    });
            }
            // Update on page load
            document.addEventListener('DOMContentLoaded', updateCartCount);
        </script>
        @yield('scripts')
    </body>

    </html>
