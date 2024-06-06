<?php

use Carbon\CarbonImmutable as Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Mail\GenericMail;
use MailCarrier\Models\Log;

beforeEach(function () {
    // Disable auth
    Config::set('mailcarrier.api_endpoint.auth_guard', null);

    Config::set('mailcarrier.queue.force', false);
    Mail::fake();
});

it('resends emails from failed logs', function () {
    $log = Log::factory()->create([
        'status' => LogStatus::Failed,
        'created_at' => Carbon::now()->subDay(),
    ]);

    $log2 = Log::factory()->create([ // This log should not be resent
        'status' => LogStatus::Failed,
        'created_at' => Carbon::now()->subDays(2),
    ]);

    $this->artisan('mailcarrier:retry')
        ->expectsOutput('Retrying failed emails...')
        ->expectsOutput('Failed emails retried.')
        ->assertExitCode(0);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) use ($log) {
        $mail->build();

        return $mail->hasTo($log->recipient) &&
            $mail->hasSubject($log->subject) &&
            $mail->hasFrom($log->sender->email);
    });

    Mail::assertNotSent(GenericMail::class, function (GenericMail $mail) use ($log2) {
        $mail->build();

        return $mail->hasTo($log2->recipient) &&
            $mail->hasSubject($log2->subject) &&
            $mail->hasFrom($log2->sender->email);
    });

    expect($log->refresh()->status)->toBe(LogStatus::Sent);
    expect($log->error)->toBeNull();

    expect($log2->refresh()->status)->toBe(LogStatus::Failed);
});

it('resends emails from failed logs with a specific date', function () {
    $log = Log::factory()->create([
        'status' => LogStatus::Failed,
        'created_at' => '2021-01-01 00:00:00',
    ]);

    $log2 = Log::factory()->create([ // This log should not be resent
        'status' => LogStatus::Failed,
        'created_at' => '2021-01-03 00:00:00',
    ]);

    $this->artisan('mailcarrier:retry --date=2021-01-01')
        ->expectsOutput('Retrying failed emails...')
        ->expectsOutput('Failed emails retried.')
        ->assertExitCode(0);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) use ($log) {
        $mail->build();

        return $mail->hasTo($log->recipient) &&
            $mail->hasSubject($log->subject) &&
            $mail->hasFrom($log->sender->email);
    });

    Mail::assertNotSent(GenericMail::class, function (GenericMail $mail) use ($log2) {
        $mail->build();

        return $mail->hasTo($log2->recipient) &&
            $mail->hasSubject($log2->subject) &&
            $mail->hasFrom($log2->sender->email);
    });

    expect($log->refresh()->status)->toBe(LogStatus::Sent);
    expect($log->error)->toBeNull();

    expect($log2->refresh()->status)->toBe(LogStatus::Failed);
});
