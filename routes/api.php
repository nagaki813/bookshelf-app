<?php

use App\Http\Controllers\Api\BookController;
use Illuminate\Support\Facades\Route;

Route::apiResource('v1/books', BookController::class)
    ->names([
        'index' => 'api.books.index',
        'store' => 'api.books.store',
        'show' => 'api.books.show',
        'update' => 'api.books.update',
        'destroy' => 'api.books.destroy',
    ]);