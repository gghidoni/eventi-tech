<?php

use App\Http\Controllers\AddressBookController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\IsMyCommunity;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('index');
})->name('home');

// EVENTS
Route::prefix('events')->group(function () {
    Volt::route('/{event}', 'events.show')->name('events.show');
});

Route::prefix('communities')->group(function () {
    Volt::route('/{community}', 'communities.show')->name('communities.show');
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
        Volt::route('/', 'dashboard.index')->name('dashboard.index');
        Volt::route('bookmarks', 'dashboard.bookmarks')->name('dashboard.bookmarks');

        Volt::route('communities', 'dashboard.communities')
            ->name('dashboard.communities');

        Route::prefix('communities')->group(function () {
            Volt::route('/', 'dashboard.communities.index')->name('dashboard.communities.index');
            Volt::route('create', 'dashboard.communities.create')->name('dashboard.communities.create');
            Volt::route('{community}/edit', 'dashboard.communities.edit')->name('dashboard.communities.edit')->middleware(IsMyCommunity::class);
            Volt::route('events', 'dashboard.communities.events')->name('dashboard.communities.events');
        });

        Route::prefix('events')->group(function () {
            Volt::route('{event}/edit', 'dashboard.events.edit')->name('dashboard.events.edit');
            Volt::route('create', 'dashboard.events.create')->name('dashboard.events.create');
        });

    });
});

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
