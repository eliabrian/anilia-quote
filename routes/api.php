<?php

use App\Http\Controllers\API\v1\QuoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('/quote', [QuoteController::class, 'index']);
    Route::get('/quote/random', [QuoteController::class, 'random']);
    Route::post('/quote', [QuoteController::class, 'store']);
})->middleware(['throttle:api']);
