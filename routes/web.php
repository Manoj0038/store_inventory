<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\web\OrderController;

Route::get('/', function () {
    return redirect()->route('project-demo');
})->name('home');

Route::get('/project-demo', function () {
    return view('project-demo');
})->name('project-demo');



Route::prefix('orders')->name('orders.')->group(function () {

    Route::get('/create', [OrderController::class, 'create'])
        ->name('create');

    Route::post('/', [OrderController::class, 'store'])
        ->name('store');

    Route::get('/{order}/bill', [OrderController::class, 'bill'])
        ->name('bill');

    Route::post('/{order}/send-email', [OrderController::class, 'sendBillEmail'])
        ->name('send-email');

    Route::post('/{order}/payment', [OrderController::class, 'updatePayment'])
        ->name('payment');

});
