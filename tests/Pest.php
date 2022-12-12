<?php

use MailCarrier\Models\User;
use MailCarrier\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * Set the currently logged in user for the application.
 */
function actingAsUser(): TestCase
{
    return test()->actingAs(User::factory()->create());
}
