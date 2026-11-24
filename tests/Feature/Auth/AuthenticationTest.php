<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'username' => $user->username,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrorsIn('username');

    $this->assertGuest();
});

test('users can not authenticate with an NIS field', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'nis' => $user->username,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrorsIn('username');

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});
