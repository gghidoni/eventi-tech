<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('profile page requires authentication', function () {
    $response = $this->get(route('dashboard.profile'));

    $response->assertRedirect(route('login'));
});

test('profile page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard.profile'));

    $response->assertStatus(200);
});

test('user can update their name', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    Livewire::actingAs($user)
        ->test('pages::dashboard.profile')
        ->set('name', 'New Name')
        ->call('saveProfile')
        ->assertRedirect(route('dashboard.profile'));

    expect($user->fresh()->name)->toBe('New Name');
});

test('name is required', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::dashboard.profile')
        ->set('name', '')
        ->call('saveProfile')
        ->assertHasErrors(['name']);
});

test('name must be at least 2 characters', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::dashboard.profile')
        ->set('name', 'A')
        ->call('saveProfile')
        ->assertHasErrors(['name']);
});

test('user can upload avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::dashboard.profile')
        ->set('name', $user->name)
        ->set('avatar', UploadedFile::fake()->image('avatar.jpg', 300, 300))
        ->call('saveProfile')
        ->assertRedirect(route('dashboard.profile'));

    expect($user->fresh()->avatar)->not->toBeNull();
});

test('user can update password', function () {
    $user = User::factory()->create(['password' => 'OldPassword1!']);

    Livewire::actingAs($user)
        ->test('pages::dashboard.profile')
        ->set('current_password', 'OldPassword1!')
        ->set('password', 'NewPassword1!')
        ->set('password_confirmation', 'NewPassword1!')
        ->call('savePassword')
        ->assertRedirect(route('dashboard.profile'));
});

test('current password must be correct to change password', function () {
    $user = User::factory()->create(['password' => 'OldPassword1!']);

    Livewire::actingAs($user)
        ->test('pages::dashboard.profile')
        ->set('current_password', 'wrong-password')
        ->set('password', 'NewPassword1!')
        ->set('password_confirmation', 'NewPassword1!')
        ->call('savePassword')
        ->assertHasErrors(['current_password']);
});

test('new password must be confirmed', function () {
    $user = User::factory()->create(['password' => 'OldPassword1!']);

    Livewire::actingAs($user)
        ->test('pages::dashboard.profile')
        ->set('current_password', 'OldPassword1!')
        ->set('password', 'NewPassword1!')
        ->set('password_confirmation', 'DifferentPassword1!')
        ->call('savePassword')
        ->assertHasErrors(['password']);
});
