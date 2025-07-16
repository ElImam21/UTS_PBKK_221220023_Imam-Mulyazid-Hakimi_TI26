<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDetailController;
use App\Http\Controllers\AuthorsController;
use App\Http\Controllers\BooksController;
use App\Http\Controllers\LoansController;
use App\Http\Controllers\BookAuthorsController;

Route::options('/{any}', function () {
    return response()->json();
})->where('any', '.*');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/search', [UserController::class, 'search']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('users_detail', UserDetailController::class);
    Route::apiResource('authors', AuthorsController::class);
    Route::apiResource('books', BooksController::class);
    Route::apiResource('loans', LoansController::class);
    Route::get('/book_authors/search', [BookAuthorsController::class, 'search']);
    Route::apiResource('book_authors', BookAuthorsController::class);
    Route::post('/book_authors', [BookAuthorsController::class, 'store']);
    // ✅ FIXED route me
    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    });
});

Route::middleware('auth:sanctum')->post('/change-password', [UserController::class, 'changePassword']);