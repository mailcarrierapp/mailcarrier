<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use MailCarrier\Models\Log as LogModel;
use MailCarrier\Models\LogEvent;
use MailCarrier\Tests\Feature\Webhooks\Strategies\FailingStrategy;
use MailCarrier\Tests\Feature\Webhooks\Strategies\FatalFailingStrategy;
use MailCarrier\Tests\Feature\Webhooks\Strategies\TestStrategy;
use MailCarrier\Tests\Feature\Webhooks\Strategies\VerboseFailingStrategy;
use MailCarrier\Webhooks\Actions\ProcessWebhook;
use MailCarrier\Webhooks\Dto\IncomingWebhook;
use function Pest\Laravel\postJson;

beforeEach(function () {
    Config::set('mailcarrier.webhooks.strategies', [
        TestStrategy::class,
    ]);

    Log::spy();
});

it('passes correct data to ProcessWebhook action', function () {
    $this->mock(ProcessWebhook::class)
        ->shouldReceive('run')
        ->once()
        ->withArgs(function (IncomingWebhook $webhook) {
            return $webhook->headers instanceof Collection
                && $webhook->headers->get('x-custom-header') === 'custom-value'
                && $webhook->body === ['test' => 'data'];
        });

    postJson(route('mailcarrier.webhook.process'), ['test' => 'data'], ['x-custom-header' => 'custom-value'])
        ->assertOk();
});

it('processes a valid webhook successfully', function () {
    $log = LogModel::factory()->create(['message_id' => 'test-message-id']);
    expect($log->events()->count())->toBe(0);

    postJson(route('mailcarrier.webhook.process'), [], ['header1' => 'value1'])
        ->assertOk();

    expect($log->events()->first())
        ->name->toBe('test-event');

    Log::shouldNotHaveReceived('warning');
});

it('continues to next strategy when validation fails', function () {
    Config::set('mailcarrier.webhooks.strategies', [
        FailingStrategy::class,
        TestStrategy::class,
    ]);

    $log = LogModel::factory()->create(['message_id' => 'test-message-id']);
    expect($log->events()->count())->toBe(0);

    postJson(route('mailcarrier.webhook.process'), [], ['header1' => 'value1'])
        ->assertOk();

    expect($log->events()->first())
        ->name->toBe('test-event');

    Log::shouldNotHaveReceived('warning');
});

it('logs a warning when validation fails and verbose is true', function () {
    Config::set('mailcarrier.webhooks.strategies', [
        VerboseFailingStrategy::class,
        TestStrategy::class,
    ]);

    $log = LogModel::factory()->create(['message_id' => 'test-message-id']);
    expect($log->events()->count())->toBe(0);

    postJson(route('mailcarrier.webhook.process'), [], ['header1' => 'value1'])
        ->assertOk();

    expect($log->events()->first())
        ->name->toBe('test-event');

    Log::shouldHaveReceived('warning')->with('Webhook validation failed', [
        'strategy' => 'VerboseFailingStrategy',
    ]);
});

it('throws an exception when validation fails and fatal is true', function () {
    Config::set('mailcarrier.webhooks.strategies', [
        FatalFailingStrategy::class,
    ]);

    $log = LogModel::factory()->create(['message_id' => 'test-message-id']);
    expect($log->events()->count())->toBe(0);

    postJson(route('mailcarrier.webhook.process'), [], ['header1' => 'value1'])
        ->assertUnprocessable()
        ->assertJson(['message' => 'Webhook validation failed.']);

    expect($log->events()->count())->toBe(0);
});

it('does nothing when no strategy validates', function () {
    Config::set('mailcarrier.webhooks.strategies', [
        FailingStrategy::class,
        FailingStrategy::class,
    ]);

    $log = LogModel::factory()->create(['message_id' => 'test-message-id']);
    expect($log->events()->count())->toBe(0);

    postJson(route('mailcarrier.webhook.process'), [], ['header1' => 'value1'])
        ->assertOk();

    expect($log->events()->count())->toBe(0);
    Log::shouldNotHaveReceived('warning');
});

it('does nothing when log is not found', function () {
    expect(LogEvent::count())->toBe(0);

    postJson(route('mailcarrier.webhook.process'), [], ['header1' => 'value1'])
        ->assertOk();

    expect(LogEvent::count())->toBe(0);
    Log::shouldNotHaveReceived('warning');
});
