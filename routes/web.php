<?php

use Illuminate\Support\Facades\Route;
<<<<<<< HEAD
=======
use App\Http\Controllers\PaymentController;
>>>>>>> c854fd0 (prueba laravel)

Route::get('/', function () {
    return view('welcome');
});
<<<<<<< HEAD
=======

Route::post('/process-easymoney', [PaymentController::class, 'processEasyMoney']);
Route::post('/pay-superwalletz', [PaymentController::class, 'paySuperWalletz']);
Route::post('/superwalletz-callback', [PaymentController::class, 'superWalletzCallback']);
Route::post('/deposit', [PaymentController::class, 'deposit']);
>>>>>>> c854fd0 (prueba laravel)
