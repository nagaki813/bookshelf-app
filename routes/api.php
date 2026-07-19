<?php

use App\Http\Controllers\Api\BookController;
use Illuminate\Support\Facades\Route;

Route::apiResource('v1/books', BookController::class)
    ->only(['index', 'show'])
    ->names([
        'index' => 'api.books.index',
        'show' => 'api.books.show',
    ]);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('v1/books', BookController::class)
        ->only(['store', 'update', 'destroy'])
        ->names([
            'store' => 'api.books.store',
            'update' => 'api.books.update',
            'destroy' => 'api.books.destroy',
        ]);
});
