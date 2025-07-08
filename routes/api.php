<?php

use App\Http\Controllers\Api\BookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api.token')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);

    Route::patch('/bookings/{booking}/slots/{slot}', [BookingController::class, 'updateSlot']);
    Route::post('/bookings/{booking}/slots', [BookingController::class, 'addSlot']);
});
