<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Real-time Bakong KHQR status check endpoint (Polled every 3s by frontend KHQR page)
Route::get('/payments/check-status/{orderNumber}', [PaymentController::class, 'checkStatus'])->name('api.payment.status');

// Quick product preview
Route::get('/products/{id}/quick-view', [ShopController::class, 'quickView']);

// User details if token authenticated
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
