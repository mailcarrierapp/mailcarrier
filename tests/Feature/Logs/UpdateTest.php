<?php

use MailCarrier\Actions\Logs\Update;
use MailCarrier\Models\Log;

it('updates the log', function () {
    /** @var Log */
    $log = Log::factory()->create([
        'trigger' => 'foo',
    ]);

    expect($log->trigger)->toBe('foo');

    $result = Update::resolve()->run($log, [
        'trigger' => 'bar',
    ]);

    expect($result->makeHidden('trigger')->toArray())->toEqualCanonicalizing($log->makeHidden('trigger')->toArray());
    expect($result->trigger)->toBe('bar');
});
