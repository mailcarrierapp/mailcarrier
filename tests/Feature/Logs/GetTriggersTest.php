<?php

use MailCarrier\Actions\Logs\GetTriggers;
use MailCarrier\Models\Log;

it('returns the distinct triggers', function () {
    Log::factory()->create([
        'trigger' => null,
    ]);

    Log::factory()->create([
        'trigger' => 'foo',
    ]);

    Log::factory()->create([
        'trigger' => 'foo',
    ]);

    Log::factory()->create([
        'trigger' => 'bar',
    ]);

    expect(GetTriggers::resolve()->run())->toEqualCanonicalizing(['foo', 'bar']);
});
