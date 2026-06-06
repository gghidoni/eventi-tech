<?php

use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider as SocialiteProvider;

test('guests can be redirected to google oauth provider', function () {
    $provider = Mockery::mock(SocialiteProvider::class);
    $provider->shouldReceive('scopes')->once()->with(['openid', 'profile', 'email'])->andReturnSelf();
    $provider->shouldReceive('with')->once()->with(['prompt' => 'select_account'])->andReturnSelf();
    $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/v2/auth'));

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $response = $this->get(route('social.redirect', ['provider' => 'google']));

    $response->assertRedirect('https://accounts.google.com/o/oauth2/v2/auth');
});

test('guests can be redirected to github oauth provider', function () {
    $provider = Mockery::mock(SocialiteProvider::class);
    $provider->shouldReceive('scopes')->once()->with(['user:email'])->andReturnSelf();
    $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://github.com/login/oauth/authorize'));

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    $response = $this->get(route('social.redirect', ['provider' => 'github']));

    $response->assertRedirect('https://github.com/login/oauth/authorize');
});

test('social callback authenticates user already linked by provider id', function () {
    $user = User::factory()->withoutTwoFactor()->create([
        'email'     => 'linked@example.com',
        'github_id' => 'gh-linked-id',
    ]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->once()->andReturn('gh-linked-id');
    $socialiteUser->shouldReceive('getEmail')->once()->andReturn('linked@example.com');
    $socialiteUser->shouldReceive('getName')->never();
    $socialiteUser->shouldReceive('getNickname')->never();

    $provider = Mockery::mock(SocialiteProvider::class);
    $provider->shouldReceive('scopes')->once()->with(['user:email'])->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    $response = $this->get(route('social.callback', ['provider' => 'github']));

    $response->assertRedirect(route('dashboard.index'));
    $this->assertAuthenticatedAs($user);
});

test('social callback links existing user by email when provider id is missing', function () {
    $user = User::factory()->withoutTwoFactor()->unverified()->create([
        'email'     => 'same-email@example.com',
        'github_id' => null,
    ]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->once()->andReturn('gh-new-id');
    $socialiteUser->shouldReceive('getEmail')->once()->andReturn('same-email@example.com');
    $socialiteUser->shouldReceive('getName')->never();
    $socialiteUser->shouldReceive('getNickname')->never();

    $provider = Mockery::mock(SocialiteProvider::class);
    $provider->shouldReceive('scopes')->once()->with(['user:email'])->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    $response = $this->get(route('social.callback', ['provider' => 'github']));

    $response->assertRedirect(route('dashboard.index'));
    $this->assertAuthenticatedAs($user->fresh());
    expect($user->fresh()?->github_id)->toBe('gh-new-id');
    expect($user->fresh()?->email_verified_at)->not()->toBeNull();
});

test('social callback creates a new user when no local match exists', function () {
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->once()->andReturn('google-new-id');
    $socialiteUser->shouldReceive('getEmail')->once()->andReturn('new-social@example.com');
    $socialiteUser->shouldReceive('getName')->once()->andReturn('Nuovo Social');

    $provider = Mockery::mock(SocialiteProvider::class);
    $provider->shouldReceive('scopes')->once()->with(['openid', 'profile', 'email'])->andReturnSelf();
    $provider->shouldReceive('with')->once()->with(['prompt' => 'select_account'])->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $response = $this->get(route('social.callback', ['provider' => 'google']));

    $createdUser = User::query()->where('email', 'new-social@example.com')->first();

    $response->assertRedirect(route('dashboard.index'));
    expect($createdUser)->not()->toBeNull();
    expect($createdUser?->google_id)->toBe('google-new-id');
    expect($createdUser?->email_verified_at)->not()->toBeNull();
    $this->assertAuthenticatedAs($createdUser);
});

test('social callback redirects back to login when provider email is missing', function () {
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->once()->andReturn('gh-id-without-email');
    $socialiteUser->shouldReceive('getEmail')->once()->andReturn(null);

    $provider = Mockery::mock(SocialiteProvider::class);
    $provider->shouldReceive('scopes')->once()->with(['user:email'])->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    $response = $this->get(route('social.callback', ['provider' => 'github']));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', __('auth.social.errors.generic'));
    $this->assertGuest();
});
