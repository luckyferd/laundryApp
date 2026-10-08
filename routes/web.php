<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/home', HomeController::class)->name('home');

    // Admin & kasir: operasional harian
    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::patch('/orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
        Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('customers', CustomerController::class)->except(['show', 'destroy']);
    });

    // Khusus admin: dashboard, laporan, master data, pengguna
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports/income', [ReportController::class, 'income'])->name('reports.income');
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::resource('services', ServiceController::class)->except('show');
        Route::resource('users', UserController::class)->except('show');
    });
});
