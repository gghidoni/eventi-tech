<?php

use App\Http\Controllers\AddressBookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\TagController;
use App\Models\Event;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('home');

// EVENTS
Route::prefix('events')->group(function () {
    Route::livewire('/{event}/cfp/apply', 'pages::events.cfp.apply')
        ->middleware(['auth', 'verified'])
        ->name('events.cfp.apply');
    Route::livewire('/{event}', 'pages::events.show')
        ->can('viewPublic', 'event')
        ->name('events.show');
});

Route::prefix('communities')->group(function () {
    Route::livewire('/{community}', 'pages::communities.show')
        ->can('viewPublic', 'community')
        ->name('communities.show');
});

Route::get('/find-location', [AddressBookController::class, 'findLocation'])->name('find');
Route::get('/find-tags', [TagController::class, 'findTags'])->name('find.tags');

// AUTH
Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::auth.login')->name('login');
    Route::livewire('/register', 'pages::auth.register')->name('register');
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->whereIn('provider', ['google', 'github'])
        ->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->whereIn('provider', ['google', 'github'])
        ->name('social.callback');
});

Route::livewire('/thanks-register', 'pages::auth.thanks-register')->name('thanks-register');

// Quando utente non è verificato e visita rotte verified
Route::livewire('/email/verify', 'pages::auth.verify-email')->middleware('auth')->name('verification.notice');

// Link di ritorno da mail di verifica
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'redirectEmailVerification'])->middleware(['auth', 'signed'])->name('verification.verify');

Route::middleware(['auth', 'verified'])->group(function () {

    // DASHBOARD
    Route::prefix('dashboard')->group(function () {
        Route::livewire('/', 'pages::dashboard.index')->name('dashboard.index');
        Route::livewire('bookmarks', 'pages::dashboard.bookmarks')->name('dashboard.bookmarks');
        Route::livewire('profile', 'pages::dashboard.profile')->name('dashboard.profile');

        Route::prefix('communities')->group(function () {
            Route::livewire('/', 'pages::dashboard.communities.index')->name('dashboard.communities.index');
            Route::livewire('create', 'pages::dashboard.communities.create')->name('dashboard.communities.create');
            Route::livewire('{community}/edit', 'pages::dashboard.communities.edit')
                ->can('update', 'community')
                ->name('dashboard.communities.edit');
            Route::livewire('events', 'pages::dashboard.communities.events')->name('dashboard.communities.events');
            Route::livewire('submissions', 'pages::dashboard.communities.submissions')->name('dashboard.communities.submissions');
        });

        Route::prefix('events')->group(function () {
            Route::livewire('{event}/edit', 'pages::dashboard.events.edit')
                ->can('update', 'event')
                ->name('dashboard.events.edit');
            Route::livewire('create', 'pages::dashboard.events.create')
                ->can('create', Event::class)
                ->name('dashboard.events.create');
        });

        Route::livewire('cfp-submissions', 'pages::dashboard.cfp-submissions')->name('dashboard.cfp-submissions');

        Route::prefix('cfps')->group(function () {
            Route::livewire('{cfp}/submissions/{submission}', 'pages::dashboard.cfps.submission')->name('dashboard.cfps.submission');
        });

    });
});
