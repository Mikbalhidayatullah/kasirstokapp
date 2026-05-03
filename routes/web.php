<?php

use App\Enums\UserRole;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Cashier\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\Stock\CategoryController;
use App\Http\Controllers\Stock\ProductController;
use App\Http\Controllers\Stock\ProductLabelController;
use App\Http\Controllers\Stock\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::middleware('role:'.UserRole::Admin->value.','.UserRole::Stock->value)->group(function (): void {
        Route::get('/stok/kategori', [CategoryController::class, 'index'])->name('stock.categories.index');
        Route::post('/stok/kategori', [CategoryController::class, 'store'])->name('stock.categories.store');
        Route::patch('/stok/kategori/{category}', [CategoryController::class, 'update'])->name('stock.categories.update');
        Route::delete('/stok/kategori/{category}', [CategoryController::class, 'destroy'])->name('stock.categories.destroy');

        Route::get('/stok/produk', [ProductController::class, 'index'])->name('stock.products.index');
        Route::post('/stok/produk', [ProductController::class, 'store'])->name('stock.products.store');
        Route::patch('/stok/produk/{product}', [ProductController::class, 'update'])->name('stock.products.update');
        Route::delete('/stok/produk/{product}', [ProductController::class, 'destroy'])->name('stock.products.destroy');
        Route::get('/stok/produk/{product}/label', [ProductLabelController::class, 'show'])->name('stock.products.label');

        Route::get('/stok/mutasi', [StockMovementController::class, 'index'])->name('stock.movements.index');
        Route::post('/stok/mutasi', [StockMovementController::class, 'store'])->name('stock.movements.store');
    });

    Route::middleware('role:'.UserRole::Admin->value.','.UserRole::Cashier->value)->group(function (): void {
        Route::get('/kasir', [CheckoutController::class, 'index'])->name('cashier.index');
        Route::post('/kasir/checkout', [CheckoutController::class, 'store'])->name('cashier.checkout');
        Route::get('/penjualan', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/penjualan/{sale}/nota', [SaleController::class, 'receipt'])->name('sales.receipt');
        Route::get('/laporan/penjualan', [SalesReportController::class, 'index'])->name('reports.sales.index');
        Route::get('/laporan/penjualan/export', [SalesReportController::class, 'export'])->name('reports.sales.export');
    });
});
