<?php

use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PurchaseLedgerController as AdminPurchaseLedgerController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SalesLedgerController as AdminSalesLedgerController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryProductsController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductCatalogController;
use App\Http\Controllers\SeoController;
use App\Http\Middleware\AuthenticateAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::view('/terms', 'legal.terms')->name('terms');
Route::get('/products', [ProductCatalogController::class, 'index'])->name('products.index');
Route::get('/new-arrivals', [ProductCatalogController::class, 'newArrivals'])->name('products.new-arrivals');
Route::get('/products/{product}', [ProductCatalogController::class, 'show'])->name('products.show');
Route::get('/category/{category}', [CategoryProductsController::class, 'show'])->name('categories.show');

Route::prefix('myadmin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->middleware('throttle:3,1')->name('login.store');

    Route::middleware(AuthenticateAdmin::class)->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/products/{product}/stock-adjustments', [AdminProductController::class, 'stockAdjustments'])->name('products.stock-adjustments.index');
        Route::post('/products/{product}/stock-adjustments', [AdminProductController::class, 'storeStockAdjustment'])->name('products.stock-adjustments.store');
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
        Route::get('/orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');
        Route::resource('users', AdminUserController::class)->only(['index', 'edit', 'update']);
        Route::get('/suppliers', [AdminPurchaseLedgerController::class, 'suppliers'])->name('suppliers.index');
        Route::get('/suppliers/create', [AdminPurchaseLedgerController::class, 'createSupplier'])->name('suppliers.create');
        Route::post('/suppliers', [AdminPurchaseLedgerController::class, 'storeSupplier'])->name('suppliers.store');
        Route::get('/kharid-khata', [AdminPurchaseLedgerController::class, 'index'])->name('purchase-ledger.index');
        Route::get('/kharid-khata/suppliers/{supplier}', [AdminPurchaseLedgerController::class, 'showSupplier'])->name('purchase-ledger.suppliers.show');
        Route::get('/kharid-khata/purchases/create', [AdminPurchaseLedgerController::class, 'createPurchase'])->name('purchase-ledger.purchases.create');
        Route::post('/kharid-khata/purchases', [AdminPurchaseLedgerController::class, 'storePurchase'])->name('purchase-ledger.purchases.store');
        Route::post('/kharid-khata/suppliers/{supplier}/purchases/{purchase}/payments', [AdminPurchaseLedgerController::class, 'storeSupplierPayment'])->name('purchase-ledger.payments.store');
        Route::get('/bikri-khata', [AdminSalesLedgerController::class, 'index'])->name('sales-ledger.index');
        Route::post('/bikri-khata/orders/{order}/payments', [AdminSalesLedgerController::class, 'storePayment'])->name('sales-ledger.payments.store');
    });
});

Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::put('/theme', [DashboardController::class, 'updateTheme'])->name('theme.update');
    Route::put('/password', [DashboardController::class, 'updatePassword'])->name('password.update');
});

Route::middleware('auth')->prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/products/{product}', [CartController::class, 'store'])->name('items.store');
    Route::put('/items/{cartItem}', [CartController::class, 'update'])->name('items.update');
    Route::delete('/items/{cartItem}', [CartController::class, 'destroy'])->name('items.destroy');
});

Route::middleware('auth')->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/', [CheckoutController::class, 'store'])->name('store');
});

Route::middleware('auth')->get('/orders/{orderNumber}/confirmation', [OrderController::class, 'confirmation'])
    ->name('orders.confirmation');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

Route::get('/forget-password', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('password.request');
Route::post('/forget-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Email Verification Routes
Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/verification-notification', [VerificationController::class, 'resend'])->name('verification.send');

// Password Confirmation Routes
Route::get('/password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
Route::post('/password/confirm', [ConfirmPasswordController::class, 'confirm'])->name('password.confirm.submit');
