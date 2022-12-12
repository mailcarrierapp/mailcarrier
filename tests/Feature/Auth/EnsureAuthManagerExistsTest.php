<?php

use MailCarrier\Actions\Auth\EnsureAuthManagerExists;
use MailCarrier\Models\User;
use function Pest\Laravel\assertDatabaseCount;

it('creates the auth manager user if does not exist', function () {
    assertDatabaseCount(User::class, 0);

    $result = EnsureAuthManagerExists::resolve()->run();

    assertDatabaseCount(User::class, 1);
    expect($result->email)->toBe('auth@mailcarrier.app');
});

it('returns the auth manager user if already exists', function () {
    $user = User::factory()->create([
        'email' => 'auth@mailcarrier.app',
    ]);

    assertDatabaseCount(User::class, 1);

    $result = EnsureAuthManagerExists::resolve()->run();

    assertDatabaseCount(User::class, 1);
    expect($result->id)->toBe($user->id);
});
