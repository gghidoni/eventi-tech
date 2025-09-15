<?php

use App\Http\Controllers\Api\V1\AddressBookController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/events/{eventId}/toggle-bookmark', [EventController::class, 'toggleBookmark']);
    Route::prefix('users')->group(function () {
        Route::get('/bookmarks', [UserController::class, 'getBookmarks']);
    });
});

Route::apiResource('events', EventController::class);

Route::get('/address_book', [AddressBookController::class, 'search']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
