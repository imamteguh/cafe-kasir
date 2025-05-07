<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/login', 'login')->name('login');

Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
 
    return redirect('/');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Volt::route('/dashboard', 'index')->name('dashboard');
    Volt::route('/users', 'users.index')->name('users');
    Volt::route('/categories', 'categories.index')->name('categories');
    Volt::route('/products', 'products.index')->name('products');
    Volt::route('/sales','sales.index')->name('sales');
    Volt::route('/sales/{sale}','sales.detail')->name('sales.detail');
});

Route::middleware(['auth', 'role:kasir,admin'])->group(function () {
    Volt::route('/', 'kasir.index');
    Volt::route('/category/{categoryId}', 'kasir.category');
});
