# Dokumentasi Proyek CafeIn2

## 1. Judul Proyek

**CafeIn2** - Sistem Manajemen Kafe berbasis web yang memungkinkan pengelolaan menu, pesanan, pembayaran, dan pengguna dengan peran berbeda. Sistem ini dibangun menggunakan framework Laravel untuk memfasilitasi operasional kafe secara efisien.

## 2. Deskripsi Singkat Proyek

-   **Fungsi Utama**: Sistem ini dirancang untuk mengelola seluruh operasional kafe, termasuk:
    -   Pengelolaan menu dan kategori produk.
    -   Proses pemesanan dan pembayaran secara online.
    -   Manajemen inventori (stok menu).
    -   Dashboard untuk monitoring performa kafe (revenue, pesanan harian, dll.).
    -   Sistem keranjang belanja untuk pelanggan.
-   **Pengguna Target**:
    -   **Admin**: Pengelola sistem yang bertanggung jawab atas konfigurasi dan pengawasan keseluruhan.
    -   **Kasir**: Staf kafe yang menangani transaksi harian di lokasi.
    -   **Customer**: Pelanggan yang mengakses sistem untuk memesan menu secara daring.
-   **Masalah yang Diselesaikan**:
    -   Mengurangi kesalahan manual dalam pemesanan dan pembayaran.
    -   Mempercepat proses transaksi dengan sistem digital.
    -   Memberikan wawasan real-time melalui dashboard untuk pengambilan keputusan bisnis.
    -   Memungkinkan pelanggan memesan dari mana saja tanpa harus datang ke kafe.

## 3. Peran (Role) dalam Proyek

Sistem ini menggunakan model peran berbasis database untuk mengontrol akses pengguna. Ada tiga peran utama:

-   **Admin**:
    -   Akses penuh ke semua fitur sistem.
    -   Dapat mengelola pengguna (create, edit, delete), menu, kategori, dan melihat laporan lengkap.
    -   Controller terkait: AdminDashboardController, Admin/UserController.
    -   View: dashboard/admin.blade.php, admin/users/index.blade.php, dll.
-   **Kasir**:
    -   Fokus pada penanganan transaksi harian.
    -   Dapat melihat dan mengelola pesanan, pembayaran, dan dashboard kasir.
    -   Tidak memiliki akses untuk mengelola pengguna atau menu.
    -   Controller terkait: CashierDashboardController, OrderController, PaymentController.
    -   View: dashboard/kasir.blade.php, orders/index.blade.php, dll.
-   **Customer**:
    -   Akses terbatas untuk fitur pelanggan.
    -   Dapat melihat menu, menambah ke keranjang, memesan, dan melacak pesanan.
    -   Controller terkait: MenuController, CartController, OrderController (khusus pelanggan).
    -   View: menus.blade.php, cart.blade.php, orders/show.blade.php, dll.

**Perbedaan Fitur/Akses**:

-   Admin vs Kasir: Admin dapat mengubah data master (pengguna, menu), sedangkan Kasir hanya dapat melihat dan memproses transaksi.
-   Kasir vs Customer: Kasir dapat mengubah status pesanan dan pembayaran, sedangkan Customer hanya dapat melihat pesanan mereka sendiri.
-   Customer: Tidak dapat mengakses dashboard admin atau kasir, hanya fitur publik dan akun pribadi.

## 4. Autentikasi & Verifikasi

-   **Proses Login**:
    -   Pengguna memasukkan email dan password melalui form login (auth/login.blade.php).
    -   Sistem menggunakan Laravel's Auth::attempt() untuk verifikasi kredensial.
    -   Password disimpan dalam database dalam bentuk hash menggunakan bcrypt (Laravel's Hash::make()), yang memastikan keamanan dengan one-way hashing.
    -   Jika berhasil, session dibuat dan pengguna di-redirect berdasarkan peran (redirectByRole() di AuthController).
    -   Fitur "Remember Me" tersedia untuk memperpanjang session.
-   **Verifikasi Email**:
    -   Saat register, email tidak diverifikasi melalui email konfirmasi.
    -   Sebaliknya, email_verified_at di-set otomatis ke waktu sekarang (now()) di AuthController::register().
    -   Ini berarti semua akun baru langsung dianggap terverifikasi, tanpa proses email verification yang umum di Laravel.

## 5. Validasi

Validasi dilakukan di dua lapisan utama untuk memastikan data input aman dan valid.

-   **Sisi View (Frontend)**:
    -   Menggunakan Blade templates dengan HTML5 validation dan Laravel's old input untuk menampilkan error.
    -   Contoh: Form login memvalidasi email (required, format email) dan password (required).
    -   Form register: name (required, max 255), email (required, unique), phone (nullable, unique), password (required, min 8, confirmed).
-   **Sisi Controller (Backend)**:
    -   Menggunakan $request->validate() di controller seperti AuthController.
    -   Contoh validasi:
        -   Login: email required dan email format, password required.
        -   Register: name required string max 255, email required email max 255 unique, phone nullable string max 20 unique, password required confirmed min 8.
        -   Reset Password: token required, email required email, password required confirmed min 8.
    -   Validasi lain: Di AdminDashboardController, query untuk status pesanan (pending, processing) memastikan hanya data valid yang ditampilkan.
    -   Di MenuController atau UserController, validasi input form untuk create/edit menu/pengguna, termasuk unique constraints dan format data.

## 6. Session & Otorisasi

-   **Pengelolaan Session**:
    -   Menggunakan Laravel's default session driver (file-based, disimpan di storage/framework/sessions).
    -   Session dibuat saat login berhasil (Auth::attempt()) dan di-regenerate untuk keamanan.
    -   Session menyimpan data pengguna dan digunakan untuk autentikasi di seluruh aplikasi.
    -   Logout: Session di-invalidate dan token di-regenerate.
-   **Otorisasi**:
    -   Menggunakan middleware EnsureRole (app/Http/Middleware/EnsureRole.php) yang memeriksa role pengguna sebelum akses route.
    -   Jika role tidak sesuai, redirect ke route('home').
    -   Contoh: Route admin dilindungi dengan middleware 'role:Admin', kasir dengan 'role:Kasir'.
    -   Tidak menggunakan role checking di dalam controller, melainkan middleware untuk efisiensi.

## 7. Data Master

Data master adalah entitas utama yang menyimpan informasi dasar sistem. Berikut detailnya berdasarkan model Laravel:

-   **Roles** (app/Models/Role.php):
    -   Field: id, name (Admin, Kasir, Customer).
    -   Relasi: hasMany Users.
    -   Digunakan untuk mengontrol akses berdasarkan peran.
-   **Users** (app/Models/User.php):
    -   Field: id, name, email, password (hashed), phone, role_id, email_verified_at, remember_token.
    -   Relasi: belongsTo Role, hasMany Orders.
    -   Menggunakan Sanctum untuk API tokens.
-   **Categories** (app/Models/Category.php):
    -   Field: id, name, description.
    -   Relasi: hasMany Menus.
    -   Mengelompokkan menu (e.g., Makanan, Minuman).
-   **Menus** (app/Models/Menu.php):
    -   Field: id, name, description, price (decimal), stock, is_active (boolean), image, category_id, metadata (array).
    -   Relasi: belongsTo Category, hasMany OrderItems, hasMany MenuImages.
    -   Metadata menyimpan informasi tambahan seperti alergen.
-   **Orders** (app/Models/Order.php):
    -   Field: id, user_id, total, status (pending, processing, completed, dll.), created_at.
    -   Relasi: belongsTo User, hasMany OrderItems, hasOne Payment.
-   **Payments** (app/Models/Payment.php):
    -   Field: id, order_id, amount, method (cash, card, dll.), status (pending, success, failed).
    -   Relasi: belongsTo Order.
-   **Carts & CartItems** (app/Models/Cart.php, CartItem.php):
    -   Cart: id, user_id, session_id.
    -   CartItem: id, cart_id, menu_id, quantity.
    -   Relasi: Cart hasMany CartItems, CartItem belongsTo Menu.
    -   Untuk menyimpan keranjang belanja sebelum checkout.
-   **OrderItems** (app/Models/OrderItem.php):
    -   Field: id, order_id, menu_id, quantity, price.
    -   Relasi: belongsTo Order, belongsTo Menu.
-   **MenuImages** (app/Models/MenuImage.php):
    -   Field: id, menu_id, image_path.
    -   Relasi: belongsTo Menu.
    -   Untuk multiple gambar per menu.

## 8. Fitur Tambahan

-   **Penggunaan API**:
    -   Menggunakan Laravel Sanctum untuk autentikasi API internal.
    -   Memungkinkan integrasi dengan aplikasi mobile atau external services.
    -   API routes di routes/api.php, menggunakan token-based auth.
-   **Fitur Ekstra**:
    -   **Dashboard**: Admin melihat revenue, top menus, recent orders, payment breakdown. Kasir melihat orders hari ini, pending orders.
    -   **Tracking Pesanan**: Customer dapat melacak status pesanan di orders/track.blade.php.
    -   **Reset Password**: Menggunakan Laravel's Password facade untuk reset via email.
    -   **Pagination**: Pada list data (menus, users) menggunakan Laravel's paginate().
    -   **Keranjang Belanja**: CartController mengelola add/remove item, checkout.
    -   **Upload Gambar**: Untuk menu, menggunakan storage untuk menyimpan gambar.

## 9. Teknologi yang Digunakan

-   **Framework**: Laravel versi 10.10 (dari composer.json, menggunakan PHP ^8.1).
-   **Database**: MySQL, dikonfigurasi di config/database.php. Menggunakan Eloquent ORM untuk interaksi database.
-   **Migration**: Laravel migrations untuk schema database, contoh:
    -   roles.php: Membuat tabel roles.
    -   users.php: Tabel users dengan foreign key ke roles.
    -   categories.php, menus.php, orders.php, payments.php, carts, dll.
    -   Migration tambahan: add_metadata_to_menus.php, add_is_active_to_menus.php.
-   **Dependencies Utama** (dari composer.json):
    -   laravel/framework: ^10.10
    -   laravel/sanctum: ^3.3 (untuk API auth)
    -   guzzlehttp/guzzle: ^7.2 (untuk HTTP requests)
    -   fakerphp/faker: ^1.9.1 (untuk seeding data)
    -   phpunit/phpunit: ^10.1 (untuk testing)
-   **Frontend**: Blade templates, CSS (resources/css/app.css), JS (resources/js/app.js), menggunakan Bootstrap (dari js/bootstrap.js).
-   **Tools Lain**: Laravel Tinker untuk debugging, Laravel Sail untuk development environment (dev dependency).
