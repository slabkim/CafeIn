<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CashierDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

Route::get('/', HomeController::class)->name('home');
Route::get('/menus', MenuController::class)->name('menus');
// Public route: return cart count (0 for guests). Keep it public to avoid creating
// an "intended" redirect when the client fetches this while unauthenticated.
Route::get('/cart/count', function () {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['count' => 0]);
    }

    $cart = App\Models\Cart::firstWhere('user_id', $user->id);
    if (!$cart) {
        return response()->json(['count' => 0]);
    }

    $count = $cart->cartItems()->sum('quantity');
    return response()->json(['count' => $count]);
})->name('cart.count');

Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');
    Route::get('/admin/menus/create', [MenuController::class, 'create'])->name('admin.menus.create');
    Route::post('/admin/menus', [MenuController::class, 'store'])->name('admin.menus.store');
    Route::get('/admin/menus', [MenuController::class, 'adminIndex'])->name('admin.menus.index');
    Route::get('/admin/menus/{menu}/edit', [MenuController::class, 'edit'])->name('admin.menus.edit');
    Route::put('/admin/menus/{menu}', [MenuController::class, 'update'])->name('admin.menus.update');
    Route::delete('/admin/menus/{menu}', [MenuController::class, 'destroy'])->name('admin.menus.destroy');
    Route::delete('/admin/menus/{menu}/images/{image}', [MenuController::class, 'deleteImage'])->name('admin.menus.images.destroy');
    Route::post('/admin/menus/{menu}/toggle-active', [MenuController::class, 'toggleActive'])->name('admin.menus.toggle');
    // User management
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::post('/admin/users/bulk', [AdminUserController::class, 'bulk'])->name('admin.users.bulk');
    Route::post('/admin/users/{user}/reset-link', [AdminUserController::class, 'sendReset'])->name('admin.users.reset');
    Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
});
Route::middleware(['auth', 'role:Kasir'])->get('/kasir/dashboard', CashierDashboardController::class)
    ->name('kasir.dashboard');

Route::middleware('auth')->group(function () {
    Route::middleware(['role:Customer,Kasir'])->group(function () {
        Route::get('/cart', CartController::class)->name('cart');
        Route::post('/cart/add/{menu}', [CartController::class, 'add'])->name('cart.add');
        Route::patch('/cart/item/{cartItem}', [CartController::class, 'updateQuantity'])->name('cart.update');
        Route::delete('/cart/item/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    });
    Route::get('/orders', OrderController::class)->name('orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/track', [OrderController::class, 'track'])->name('orders.track');
    Route::get('/payments', PaymentController::class)->name('payments');
    Route::post('/payments/complete', [PaymentController::class, 'complete'])->name('payments.complete');
    Route::post('/payments/save-info', [PaymentController::class, 'saveInfo'])->name('payments.saveInfo');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    // Password reset
    Route::get('/password/reset/{token}', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/password/reset', [AuthController::class, 'reset'])->name('password.update');
});
