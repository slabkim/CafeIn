<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::view('/menus', 'menus')->name('menus');
Route::view('/orders', 'orders')->name('orders');
Route::view('/payments', 'payments')->name('payments');
