<?php

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [ // Custom register route
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect('/thanks-register'); // As per Fortify config
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'name'  => 'Test User',
        'email' => 'test@example.com',
    ]);
});
