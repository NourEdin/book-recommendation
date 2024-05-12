<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

//Assignment endpoints
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/recommended', [BookController::class, 'recommended']);

Route::post('/interval', [BookController::class, 'addInterval'])->middleware('auth');

//Authentication endpoints
require __DIR__.'/auth.php';
