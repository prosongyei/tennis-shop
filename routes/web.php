<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PORTAL 1: CUSTOMER ONLINE STOREFRONT (Public & Customer Account)
|--------------------------------------------------------------------------
*/

// Public Logo Asset Route
Route::get('/images/logo.png', function () {
    $path = public_path('images/logo.png');
    if (file_exists($path)) {
        return response()->file($path, ['Content-Type' => 'image/png']);
    }
    abort(404);
})->name('brand.logo');

// Health Check & DB Auto-Setup Diagnostics
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'php' => PHP_VERSION]);
});

Route::get('/debug-db', function () {
    if (!app()->isLocal()) {
        abort(403, 'Database diagnostics are disabled in production.');
    }
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbName = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $tableCount = count($tables);
        $output = "<h3>Connected successfully to Database: " . e($dbName) . "</h3><p>Tables count: " . $tableCount . "</p>";

        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output .= "<p><strong>Migrate:</strong> " . nl2br(e(\Illuminate\Support\Facades\Artisan::output())) . "</p>";

        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        $output .= "<p><strong>Seed:</strong> " . nl2br(e(\Illuminate\Support\Facades\Artisan::output())) . "</p>";

        $output .= '<p><a href="/" style="display:inline-block;padding:10px 20px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;">Go to Storefront Homepage</a></p>';
        return $output;
    } catch (\Throwable $e) {
        return "<h3>Database Connection Error:</h3><pre>" . e($e->getMessage()) . "</pre>";
    }
});

// Public Catalog & Product Detail
Route::get('/', [ShopController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'catalog'])->name('shop.catalog');
Route::get('/product/{slug}', [ShopController::class, 'show'])->name('shop.product');
Route::get('/quick-view/{id}', [ShopController::class, 'quickView'])->name('shop.quickview');

// Wishlist
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle/{id}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::delete('/wishlist/clear', [WishlistController::class, 'clear'])->name('wishlist.clear');

// Shopping Cart (Slide-over drawer & page)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/update/{id}', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{id}', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    Route::get('/count', [CartController::class, 'count'])->name('count');
});

// Checkout & Order Placement
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

// Dynamic Bakong KHQR Gateway & Simulation
Route::get('/checkout/khqr', [\App\Http\Controllers\BakongPaymentController::class, 'checkout'])->name('khqr.checkout');
Route::post('/checkout/khqr/generate', [\App\Http\Controllers\BakongPaymentController::class, 'generateQr'])->name('khqr.generate');
Route::post('/checkout/khqr/check', [\App\Http\Controllers\BakongPaymentController::class, 'checkPayment'])->name('khqr.check');
Route::get('/payment/khqr/{orderNumber}', [PaymentController::class, 'showKhqr'])->name('payment.khqr');
Route::post('/payment/khqr/{orderNumber}/currency', [PaymentController::class, 'switchCurrency'])->name('payment.khqr.currency');
Route::get('/payment/khqr/{orderNumber}/status', [PaymentController::class, 'checkStatus'])->name('payment.khqr.status');
Route::post('/payment/simulate/{orderNumber}', [PaymentController::class, 'simulateBakongPay'])->name('payment.simulate');
Route::post('/payment/upload-proof/{orderNumber}', [PaymentController::class, 'uploadProof'])->name('payment.upload-proof');

// Customer Order Tracking & Invoices
Route::get('/track-order', [OrderController::class, 'track'])->name('orders.track');
Route::get('/orders/invoice/{orderNumber}', [OrderController::class, 'invoice'])->name('orders.invoice');
Route::get('/orders/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/orders/{orderNumber}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

// Customer Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/my-account', [AuthController::class, 'profile'])->name('profile');
    Route::post('/my-account', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::get('/my-orders', [OrderController::class, 'index'])->name('orders.my');
});


/*
|--------------------------------------------------------------------------
| PORTAL 2: CASHIER POINT-OF-SALE (POS) WORKSTATION
|--------------------------------------------------------------------------
*/

// POS Staff Login
Route::get('/pos/login', [AuthController::class, 'showPosLogin'])->name('pos.login');
Route::post('/pos/login', [AuthController::class, 'posLogin']);
Route::post('/pos/logout', [AuthController::class, 'posLogout'])->name('pos.logout');

// POS Terminal Operations (Cashier & Admin only)
Route::middleware(['auth', 'role:cashier,admin'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('index');
    Route::get('/search', [PosController::class, 'search'])->name('search');
    Route::post('/process-sale', [PosController::class, 'processSale'])->name('process');
    Route::get('/receipt/{orderNumber}', [PosController::class, 'printReceipt'])->name('receipt');
});


/*
|--------------------------------------------------------------------------
| PORTAL 3: ADMIN MANAGEMENT BACK-OFFICE CONSOLE
|--------------------------------------------------------------------------
*/

// Admin Manager Login
Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/admin/logout', [AuthController::class, 'adminLogout'])->name('admin.logout');

// Admin Operations (Admin only)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Product & Stock Catalog
    Route::resource('products', AdminProductController::class);
    Route::post('products/{id}/adjust-stock', [AdminProductController::class, 'adjustStock'])->name('products.adjust-stock');

    // Order Fulfillment Workflow
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('orders/{id}/verify-payment', [AdminOrderController::class, 'verifyPayment'])->name('orders.verify-payment');
    Route::post('orders/{id}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');

    // Store & Bank Gateway Settings
    Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [AdminSettingController::class, 'update'])->name('settings.update');
    Route::get('settings/test-bakong', [AdminSettingController::class, 'testBakong'])->name('settings.test-bakong');
});
