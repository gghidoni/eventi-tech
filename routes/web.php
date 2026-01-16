<?php

use App\Http\Controllers\AddressBookController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\IsMyCommunity;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('home');

// EVENTS
Route::prefix('events')->group(function () {
    Route::livewire('/{event}', 'pages::events.show')->name('events.show');
});

Route::prefix('communities')->group(function () {
    Route::livewire('/{community}', 'pages::communities.show')->name('communities.show');
});

Route::get('/find-location', [AddressBookController::class, 'findLocation'])->name('find');

// AUTH
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::get('/thanks-register', [AuthController::class, 'thanksRegister'])->name('thanks-register');

// Quando utente non è verificato e visita rotte verified
Route::get('/email/verify', [AuthController::class, 'verificationNotice'])->middleware('auth')->name('verification.notice');

// Link di ritorno da mail di verifica
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'redirectEmailVerification'])->middleware(['auth', 'signed'])->name('verification.verify');

Route::middleware(['auth', 'verified'])->group(function () {

    // DASHBOARD
    Route::prefix('dashboard')->group(function () {
        Route::livewire('/', 'pages::dashboard.index')->name('dashboard.index');
        Route::livewire('bookmarks', 'pages::dashboard.bookmarks')->name('dashboard.bookmarks');

        Route::prefix('communities')->group(function () {
            Route::livewire('/', 'pages::dashboard.communities.index')->name('dashboard.communities.index');
            Route::livewire('create', 'pages::dashboard.communities.create')->name('dashboard.communities.create');
            Route::livewire('{community}/edit', 'pages::dashboard.communities.edit')->name('dashboard.communities.edit')->middleware(IsMyCommunity::class);
            Route::livewire('events', 'pages::dashboard.communities.events')->name('dashboard.communities.events');
        });

        Route::prefix('events')->group(function () {
            Route::livewire('{event}/edit', 'pages::dashboard.events.edit')->name('dashboard.events.edit');
            Route::livewire('create', 'pages::dashboard.events.create')->name('dashboard.events.create');
        });

    });
});
