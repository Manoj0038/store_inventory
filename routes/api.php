<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/orders', [OrderController::class, 'store'])
    ->name('api.orders.store');

// Requirement 3: API endpoint to fetch a customer's order history by email
Route::get('/customers/{email}/orders', [OrderController::class, 'customerOrderHistory'])
    ->where('email', '.*')
    ->name('api.customers.orders');

// Requirement 4: API endpoint returning products below a configurable low-stock threshold
Route::get('/products/low-stock', [ProductController::class, 'lowStock'])
    ->name('api.products.low-stock');
