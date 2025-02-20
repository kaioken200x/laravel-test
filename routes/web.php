<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/process-easymoney', [PaymentController::class, 'processEasyMoney']);
Route::post('/pay-superwalletz', [PaymentController::class, 'paySuperWalletz']);
Route::post('/superwalletz-callback', [PaymentController::class, 'superWalletzCallback']);
Route::post('/deposit', [PaymentController::class, 'deposit']);
