<?php

use MailCarrier\Models\User;
use function Pest\Laravel\assertDatabaseCount;

it('can fetch data', function () {
    assertDatabaseCount(User::class, 0);
});
