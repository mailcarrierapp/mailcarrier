<?php

use Laravel\Socialite\AbstractUser;
use MailCarrier\Actions\Auth\SocialLogin;
use MailCarrier\Models\User;
use function Pest\Faker\faker;
use function Pest\Laravel\assertDatabaseCount;

it('creates user if does not exist', function () {
    assertDatabaseCount(User::class, 0);

    $abstractUser = new class extends AbstractUser {};
    $abstractUser->id = faker()->uuid();
    $abstractUser->name = faker()->name();
    $abstractUser->email = faker()->email();
    $abstractUser->avatar = faker()->imageUrl();
    $abstractUser->user = ['foo' => 'bar'];

    SocialLogin::resolve()->run($abstractUser);

    assertDatabaseCount(User::class, 1);

    /** @var User */
    $user = User::first();

    expect($user->oauth_id)->not->toBeNull()->toBe($abstractUser->getId());
    expect($user->name)->not->toBeNull()->toBe($abstractUser->getName());
    expect($user->email)->not->toBeNull()->toBe($abstractUser->getEmail());
    expect($user->picture_url)->not->toBeNull()->toBe($abstractUser->getAvatar());
    expect($user->oauth_raw)->not->toBeNull()->toBe($abstractUser->getRaw());
});

it('updates user if already exists by oauth ID', function () {
    /** @var User */
    $user = User::factory()->create([
        'name' => 'foo',
        'email' => 'foo@example.org',
        'picture_url' => null,
        'oauth_id' => $oauthId = faker()->uuid(),
        'oauth_raw' => ['foo' => 'bar'],
    ]);

    assertDatabaseCount(User::class, 1);

    $abstractUser = new class extends AbstractUser {};
    $abstractUser->id = $oauthId;
    $abstractUser->name = faker()->name();
    $abstractUser->email = faker()->email();
    $abstractUser->avatar = faker()->imageUrl();
    $abstractUser->user = ['foo2' => 'bar2'];

    SocialLogin::resolve()->run($abstractUser);

    assertDatabaseCount(User::class, 1);

    /** @var User */
    $user = User::first();

    expect($user->oauth_id)->not->toBeNull()->toBe($oauthId);
    expect($user->name)->not->toBeNull()->toBe($abstractUser->getName());
    expect($user->email)->not->toBeNull()->toBe($abstractUser->getEmail());
    expect($user->picture_url)->not->toBeNull()->toBe($abstractUser->getAvatar());
    expect($user->oauth_raw)->not->toBeNull()->toBe($abstractUser->getRaw());
});
