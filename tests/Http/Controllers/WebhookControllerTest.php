<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use MailCarrier\Models\Log as LogModel;
use MailCarrier\Models\LogEvent;
use MailCarrier\Tests\Webhooks\Strategies\TestStrategy;
use MailCarrier\Webhooks\Actions\ProcessWebhook;
use MailCarrier\Webhooks\Dto\IncomingWebhook;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('mailcarrier.webhooks.strategies', [
        TestStrategy::class,
    ]);

    Log::spy();
});

it('processes a valid webhook successfully', function () {
    $log = LogModel::factory()->create(['message_id' => 'test-message-id']);
    expect($log->events()->count())->toBe(0);

    postJson(route('webhook'), [], ['header1' => 'value1'])
        ->assertOk();

    expect($log->events()->first())
        ->name->toBe('test-event');

    Log::shouldNotHaveReceived('warning');
});

it('continues to next strategy when validation fails', function () {
    Config::set('mailcarrier.webhooks.strategies', [
        TestStrategy::class . '::class,false,false,false', // shouldValidate=false, isVerbose=false, isFatal=false
        TestStrategy::class . '::class,true,false,false',  // shouldValidate=true, isVerbose=false, isFatal=false
    ]);

    $log = LogModel::factory()->create(['message_id' => 'test-message-id']);
    expect($log->events()->count())->toBe(0);

    postJson(route('webhook'), [], ['header1' => 'value1'])
        ->assertOk();

    expect($log->events()->first())
        ->name->toBe('test-event');

    Log::shouldNotHaveReceived('warning');
});

it('logs a warning when validation fails and verbose is true', function () {
    Config::set('mailcarrier.webhooks.strategies', [
        TestStrategy::class . '::class,false,true,false', // shouldValidate=false, isVerbose=true, isFatal=false
        TestStrategy::class . '::class,true,false,false', // shouldValidate=true, isVerbose=false, isFatal=false
    ]);

    $log = LogModel::factory()->create(['message_id' => 'test-message-id']);
    expect($log->events()->count())->toBe(0);

    postJson(route('webhook'), [], ['header1' => 'value1'])
        ->assertOk();

    expect($log->events()->first())
        ->name->toBe('test-event');

    Log::shouldHaveReceived('warning')->with('Webhook validation failed', [
        'strategy' => 'TestStrategy',
    ]);
});

it('throws an exception when validation fails and fatal is true', function () {
    Config::set('mailcarrier.webhooks.strategies', [
        TestStrategy::class . '::class,false,false,true', // shouldValidate=false, isVerbose=false, isFatal=true
    ]);

    $log = LogModel::factory()->create(['message_id' => 'test-message-id']);
    expect($log->events()->count())->toBe(0);

    postJson(route('webhook'), [], ['header1' => 'value1'])
        ->assertUnprocessable()
        ->assertJson(['message' => 'Webhook validation failed for strategy: TestStrategy']);

    expect($log->events()->count())->toBe(0);
});

it('does nothing when no strategy validates', function () {
    Config::set('mailcarrier.webhooks.strategies', [
        TestStrategy::class . '::class,false,false,false', // shouldValidate=false, isVerbose=false, isFatal=false
        TestStrategy::class . '::class,false,false,false', // shouldValidate=false, isVerbose=false, isFatal=false
    ]);

    $log = LogModel::factory()->create(['message_id' => 'test-message-id']);
    expect($log->events()->count())->toBe(0);

    postJson(route('webhook'), [], ['header1' => 'value1'])
        ->assertOk();

    expect($log->events()->count())->toBe(0);
    Log::shouldNotHaveReceived('warning');
});

it('does nothing when log is not found', function () {
    expect(LogEvent::count())->toBe(0);

    postJson(route('webhook'), [], ['header1' => 'value1'])
        ->assertOk();

    expect(LogEvent::count())->toBe(0);
    Log::shouldNotHaveReceived('warning');
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

    postJson(route('webhook'), ['test' => 'data'], ['x-custom-header' => 'custom-value'])
        ->assertOk();
});
