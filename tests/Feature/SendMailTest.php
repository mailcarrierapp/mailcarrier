<?php

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use MailCarrier\Actions\SendMail;
use MailCarrier\Dto\GenericMailDto;
use MailCarrier\Dto\RemoteAttachmentDto;
use MailCarrier\Dto\SendMailDto;
use MailCarrier\Enums\AttachmentLogStrategy;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Facades\MailCarrier;
use MailCarrier\Jobs\SendMailJob;
use MailCarrier\Mail\GenericMail;
use MailCarrier\Models\Log;
use MailCarrier\Models\Template;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\postJson;

beforeEach(function () {
    // Disable auth
    Config::set('mailcarrier.api_endpoint.auth_guard', null);

    Config::set('mailcarrier.queue.force', false);
    Mail::fake();
});

it('sends email to the recipient', function () {
    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
    ])->assertOk();

    Mail::assertSent(GenericMail::class, 1);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient@example.org') &&
            $mail->hasSubject('Welcome!');
    });

    /** @var Log */
    $log = Log::first();

    expect($log->status)->toBe(LogStatus::Sent);
    expect($log->error)->toBeNull();
});

it('compiles the variable into the template', function () {
    Template::factory()->create([
        'layout_id' => null,
        'slug' => 'welcome',
        'content' => 'Welcome {{ name }}',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
        'variables' => [
            'name' => 'foo',
        ],
    ])->assertOk();

    Mail::assertSent(GenericMail::class, 1);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient@example.org') &&
            $mail->hasSubject('Welcome!') &&
            $mail->hasHtml('Welcome foo');
    });

    /** @var Log */
    $log = Log::first();

    expect($log->status)->toBe(LogStatus::Sent);
    expect($log->error)->toBeNull();
    expect($log->variables)->toBe([
        'name' => 'foo',
    ]);
});

it('sends email to the given cc and bcc', function () {
    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
        'cc' => 'cc@example.org',
        'bcc' => 'bcc@example.org',
    ])->assertOk();

    Mail::assertSent(GenericMail::class, 1);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient@example.org') &&
            $mail->hasSubject('Welcome!') &&
            $mail->hasCc('cc@example.org') &&
            $mail->hasBcc('bcc@example.org');
    });

    /** @var Log */
    $log = Log::first();

    expect($log->status)->toBe(LogStatus::Sent);
    expect($log->cc[0]->email)->toBe('cc@example.org');
    expect($log->bcc[0]->email)->toBe('bcc@example.org');
    expect($log->error)->toBeNull();
});

it('allows multiple cc and bcc as email strings', function () {
    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
        'cc' => ['cc@example.org', 'cc2@example.org'],
        'bcc' => ['bcc@example.org', 'bcc2@example.org'],
    ])->assertOk();

    Mail::assertSent(GenericMail::class, 1);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient@example.org')
            && $mail->hasSubject('Welcome!')
            && $mail->hasCc('cc@example.org')
            && $mail->hasCc('cc2@example.org')
            && $mail->hasBcc('bcc@example.org')
            && $mail->hasBcc('bcc2@example.org');
    });

    /** @var Log */
    $log = Log::first();

    expect($log->status)->toBe(LogStatus::Sent);
    expect($log->cc)->toHaveCount(2);
    expect($log->cc[0]->email)->toBe('cc@example.org');
    expect($log->cc[1]->email)->toBe('cc2@example.org');
    expect($log->bcc)->toHaveCount(2);
    expect($log->bcc[0]->email)->toBe('bcc@example.org');
    expect($log->bcc[1]->email)->toBe('bcc2@example.org');
    expect($log->error)->toBeNull();
});

it('allows multiple cc and bcc as email contacts', function () {
    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
        'cc' => [
            ['email' => 'cc@example.org', 'name' => 'CC'],
            ['email' => 'cc2@example.org'],
        ],
        'bcc' => [
            ['email' => 'bcc@example.org', 'name' => 'BCC'],
            ['email' => 'bcc2@example.org'],
        ],
    ])->assertOk();

    Mail::assertSent(GenericMail::class, 1);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient@example.org')
            && $mail->hasSubject('Welcome!')
            && $mail->hasCc('cc@example.org', 'CC')
            && $mail->hasCc('cc2@example.org')
            && $mail->hasBcc('bcc@example.org', 'BCC')
            && $mail->hasBcc('bcc2@example.org');
    });

    /** @var Log */
    $log = Log::first();

    expect($log->status)->toBe(LogStatus::Sent);
    expect($log->cc)->toHaveCount(2);
    expect($log->cc[0]->email)->toBe('cc@example.org');
    expect($log->cc[0]->name)->toBe('CC');
    expect($log->cc[1]->email)->toBe('cc2@example.org');
    expect($log->bcc)->toHaveCount(2);
    expect($log->bcc[0]->email)->toBe('bcc@example.org');
    expect($log->bcc[0]->name)->toBe('BCC');
    expect($log->bcc[1]->email)->toBe('bcc2@example.org');
    expect($log->error)->toBeNull();
});

it('sends email from the given sender email', function () {
    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    postJson(route('mailcarrier.send'), [
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
        'sender' => 'sender@example.org',
    ])->assertOk();

    Mail::assertSent(GenericMail::class, 1);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient@example.org') &&
            $mail->hasSubject('Welcome!') &&
            $mail->hasFrom('sender@example.org');
    });

    /** @var Log */
    $log = Log::first();

    expect($log->status)->toBe(LogStatus::Sent);
    expect($log->sender->email)->toBe('sender@example.org');
    expect($log->sender->name)->toBeNull();
    expect($log->error)->toBeNull();
});

it('sends email from the given sender email and name', function () {
    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    postJson(route('mailcarrier.send'), [
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
        'sender' => [
            'email' => 'sender@example.org',
            'name' => 'Sender',
        ],
    ])->assertOk();

    Mail::assertSent(GenericMail::class, 1);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient@example.org') &&
            $mail->hasSubject('Welcome!') &&
            $mail->hasFrom('sender@example.org', 'Sender');
    });

    /** @var Log */
    $log = Log::first();

    expect($log->status)->toBe(LogStatus::Sent);
    expect($log->sender->email)->toBe('sender@example.org');
    expect($log->sender->name)->toBe('Sender');
    expect($log->error)->toBeNull();
});

it('throws error if the sender is an object without the email', function () {
    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    postJson(route('mailcarrier.send'), [
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
        'sender' => [
            'name' => 'Sender',
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrorFor('sender');

    Mail::assertNothingOutgoing();

    assertDatabaseCount(Log::class, 0);
});

it('sends multiple emails to multiple recipients', function () {
    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipients' => [
            ['email' => 'recipient1@example.org'],
            ['email' => 'recipient2@example.org'],
        ],
    ])->assertOk();

    Mail::assertSent(GenericMail::class, 2);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient1@example.org') &&
            $mail->hasSubject('Welcome!');
    });

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient2@example.org') &&
            $mail->hasSubject('Welcome!');
    });

    /** @var Log */
    $log1 = Log::firstWhere('recipient', 'recipient1@example.org');

    /** @var Log */
    $log2 = Log::firstWhere('recipient', 'recipient2@example.org');

    expect($log1->status)->toBe(LogStatus::Sent);
    expect($log1->error)->toBeNull();

    expect($log2->status)->toBe(LogStatus::Sent);
    expect($log2->error)->toBeNull();
});

it('compiles the recipient-defined variables when there are multiple recipients', function () {
    Template::factory()->create([
        'layout_id' => null,
        'slug' => 'welcome',
        'content' => 'Welcome {{ name }}',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipients' => [
            [
                'email' => 'recipient1@example.org',
                'variables' => [
                    'name' => 'foo',
                ],
            ],
            [
                'email' => 'recipient2@example.org',
                'variables' => [
                    'name' => 'bar',
                ],
            ],
        ],
    ])->assertOk();

    Mail::assertSent(GenericMail::class, 2);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient1@example.org') &&
            $mail->hasSubject('Welcome!') &&
            $mail->hasHtml('Welcome foo');
    });

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient2@example.org') &&
            $mail->hasSubject('Welcome!') &&
            $mail->hasHtml('Welcome bar');
    });

    /** @var Log */
    $log1 = Log::firstWhere('recipient', 'recipient1@example.org');

    /** @var Log */
    $log2 = Log::firstWhere('recipient', 'recipient2@example.org');

    expect($log1->status)->toBe(LogStatus::Sent);
    expect($log1->error)->toBeNull();
    expect($log1->variables)->toBe([
        'name' => 'foo',
    ]);

    expect($log2->status)->toBe(LogStatus::Sent);
    expect($log2->error)->toBeNull();
    expect($log2->variables)->toBe([
        'name' => 'bar',
    ]);
});

it('merges the recipient-defined variables with the request ones when there are multiple recipients', function () {
    Template::factory()->create([
        'layout_id' => null,
        'slug' => 'welcome',
        'content' => 'Welcome {{ name }} from {{ company }}',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'variables' => [
            'company' => 'MailCarrier',
            'name' => 'baz', // Will be discarded using the per-recipient value
        ],
        'recipients' => [
            [
                'email' => 'recipient1@example.org',
                'variables' => [
                    'name' => 'foo',
                ],
            ],
            [
                'email' => 'recipient2@example.org',
                'variables' => [
                    'name' => 'bar',
                ],
            ],
        ],
    ])->assertOk();

    Mail::assertSent(GenericMail::class, 2);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient1@example.org') &&
            $mail->hasSubject('Welcome!') &&
            $mail->hasHtml('Welcome foo from MailCarrier');
    });

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient2@example.org') &&
            $mail->hasSubject('Welcome!') &&
            $mail->hasHtml('Welcome bar from MailCarrier');
    });

    /** @var Log */
    $log1 = Log::firstWhere('recipient', 'recipient1@example.org');

    /** @var Log */
    $log2 = Log::firstWhere('recipient', 'recipient2@example.org');

    expect($log1->status)->toBe(LogStatus::Sent);
    expect($log1->error)->toBeNull();
    expect($log1->variables)->toEqualCanonicalizing([
        'company' => 'MailCarrier',
        'name' => 'foo',
    ]);

    expect($log2->status)->toBe(LogStatus::Sent);
    expect($log2->error)->toBeNull();
    expect($log2->variables)->toEqualCanonicalizing([
        'company' => 'MailCarrier',
        'name' => 'bar',
    ]);
});

it('uses the recipient-defined cc and bcc when there are multiple recipients', function () {
    Template::factory()->create([
        'layout_id' => null,
        'slug' => 'welcome',
        'content' => 'Welcome {{ name }}',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipients' => [
            [
                'email' => 'recipient1@example.org',
                'cc' => 'recipient1+cc@example.org',
                'bcc' => 'recipient1+bcc@example.org',
                'variables' => [
                    'name' => 'foo',
                ],
            ],
            [
                'email' => 'recipient2@example.org',
                'cc' => 'recipient2+cc@example.org',
                'bcc' => 'recipient2+bcc@example.org',
                'variables' => [
                    'name' => 'bar',
                ],
            ],
        ],
    ])->assertOk();

    Mail::assertSent(GenericMail::class, 2);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient1@example.org') &&
            $mail->hasSubject('Welcome!') &&
            $mail->hasCc('recipient1+cc@example.org') &&
            $mail->hasBcc('recipient1+bcc@example.org') &&
            $mail->hasHtml('Welcome foo');
    });

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient2@example.org') &&
            $mail->hasSubject('Welcome!') &&
            $mail->hasCc('recipient2+cc@example.org') &&
            $mail->hasBcc('recipient2+bcc@example.org') &&
            $mail->hasHtml('Welcome bar');
    });

    /** @var Log */
    $log1 = Log::firstWhere('recipient', 'recipient1@example.org');

    /** @var Log */
    $log2 = Log::firstWhere('recipient', 'recipient2@example.org');

    expect($log1->status)->toBe(LogStatus::Sent);
    expect($log1->error)->toBeNull();
    expect($log1->cc[0]->email)->toBe('recipient1+cc@example.org');
    expect($log1->bcc[0]->email)->toBe('recipient1+bcc@example.org');
    expect($log1->variables)->toEqualCanonicalizing([
        'name' => 'foo',
    ]);

    expect($log2->status)->toBe(LogStatus::Sent);
    expect($log2->error)->toBeNull();
    expect($log2->cc[0]->email)->toBe('recipient2+cc@example.org');
    expect($log2->bcc[0]->email)->toBe('recipient2+bcc@example.org');
    expect($log2->variables)->toEqualCanonicalizing([
        'name' => 'bar',
    ]);
});

it('throws exception when one or more template defined variables are not present', function () {
    Template::factory()->create([
        'layout_id' => null,
        'slug' => 'welcome',
        'name' => 'Welcome',
        'content' => 'Welcome {{ name }} from {{ company }}',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
        'variables' => [
            'name' => 'foo',
        ],
    ])
        ->assertStatus(Response::HTTP_PRECONDITION_FAILED)
        ->assertJson([
            'response' => 'error',
            'key' => 'MISSING_VARIABLE',
            'message' => 'Missing variable "company" for template "Welcome"',
        ]);

    Mail::assertNothingOutgoing();

    /** @var Log */
    $log = Log::first();

    expect($log->status)->toBe(LogStatus::Failed);
    expect($log->error)->toBe('Missing variable "company" for template "Welcome"');
    expect($log->variables)->toBe([
        'name' => 'foo',
    ]);
});

it('does not create any log if not requested', function () {
    Template::factory()->create([
        'layout_id' => null,
        'slug' => 'welcome',
    ]);

    SendMail::resolve()
        ->withoutLogging()
        ->run(new SendMailDto([
            'enqueue' => false,
            'template' => 'welcome',
            'subject' => 'Welcome!',
            'recipients' => [
                ['email' => 'recipient1@example.org'],
                ['email' => 'recipient2@example.org'],
            ],
        ]));

    Mail::assertSent(GenericMail::class, 2);

    assertDatabaseCount(Log::class, 0);
});

it('enqueues the mail if requested and queue is enabled', function () {
    Config::set('mailcarrier.queue.enabled', true);
    Bus::fake();

    Template::factory()->create([
        'layout_id' => null,
        'slug' => 'welcome',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => true,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipients' => [
            ['email' => 'recipient1@example.org'],
            ['email' => 'recipient2@example.org'],
        ],
    ])->assertOk();

    Mail::assertNothingOutgoing();
    Bus::assertDispatchedSync(SendMailJob::class, 0);
    Bus::assertDispatched(SendMailJob::class, 2);

    /** @var Log */
    $log1 = Log::firstWhere('recipient', 'recipient1@example.org');

    /** @var Log */
    $log2 = Log::firstWhere('recipient', 'recipient2@example.org');

    expect($log1->status)->toBe(LogStatus::Pending);
    expect($log1->error)->toBeNull();

    expect($log2->status)->toBe(LogStatus::Pending);
    expect($log2->error)->toBeNull();
});

it('enqueues the mail if not requested but forced', function () {
    Config::set('mailcarrier.queue.force', true);
    Config::set('mailcarrier.queue.enabled', true);
    Bus::fake();

    Template::factory()->create([
        'layout_id' => null,
        'slug' => 'welcome',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipients' => [
            ['email' => 'recipient1@example.org'],
            ['email' => 'recipient2@example.org'],
        ],
    ])->assertOk();

    Mail::assertNothingOutgoing();
    Bus::assertNotDispatchedSync(SendMailJob::class, 0);
    Bus::assertDispatched(SendMailJob::class, 2);

    /** @var Log */
    $log1 = Log::firstWhere('recipient', 'recipient1@example.org');

    /** @var Log */
    $log2 = Log::firstWhere('recipient', 'recipient2@example.org');

    expect($log1->status)->toBe(LogStatus::Pending);
    expect($log1->error)->toBeNull();

    expect($log2->status)->toBe(LogStatus::Pending);
    expect($log2->error)->toBeNull();
});

it('does not enqueue the mail if requested but queue is disabled', function () {
    Config::set('mailcarrier.queue.enabled', false);
    Bus::fake();

    Template::factory()->create([
        'layout_id' => null,
        'slug' => 'welcome',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipients' => [
            ['email' => 'recipient1@example.org'],
            ['email' => 'recipient2@example.org'],
        ],
    ])->assertOk();

    Mail::assertNothingOutgoing();
    Bus::assertDispatchedSync(SendMailJob::class, 2);
});

it('sends mail with regular attachments', function () {
    Config::set('mailcarrier.attachments.log_strategy', AttachmentLogStrategy::None);

    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    SendMail::resolve()->run(new SendMailDto([
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
        'attachments' => [
            $attachment1 = UploadedFile::fake()->image('foo.jpg'),
            $attachment2 = UploadedFile::fake()->image('bar.jpg'),
        ],
    ]));

    Mail::assertSent(GenericMail::class, 1);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) use ($attachment1, $attachment2) {
        $mail->build();

        return $mail->hasTo('recipient@example.org') &&
            $mail->hasSubject('Welcome!') &&
            $mail->hasAttachedData($attachment1->getContent(), 'foo.jpg') &&
            $mail->hasAttachedData($attachment2->getContent(), 'bar.jpg');
    });

    /** @var Log */
    $log = Log::first();

    expect($log->status)->toBe(LogStatus::Sent);
    expect($log->error)->toBeNull();
    expect($log->attachments()->count())->toBe(2);
    expect($log->attachments()->pluck('name')->all())->toEqualCanonicalizing([
        'foo.jpg',
        'bar.jpg',
    ]);
});

it('sends mail with remote attachments', function () {
    Config::set('mailcarrier.attachments.log_strategy', AttachmentLogStrategy::None);

    MailCarrier::partialMock()
        ->shouldReceive('getFileSize')
        ->twice()
        ->andReturn(1000);

    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    SendMail::resolve()->run(new SendMailDto([
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
        'remoteAttachments' => [
            new RemoteAttachmentDto([
                'resource' => '/path/foo.jpg',
                'name' => 'foo.jpg',
            ]),
            new RemoteAttachmentDto([
                'resource' => '/path/bar.jpg',
                'name' => 'bar.jpg',
            ]),
        ],
    ]));

    Mail::assertSent(GenericMail::class, 1);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient@example.org') &&
            $mail->hasSubject('Welcome!') &&
            $mail->hasAttachmentFromStorage('/path/foo.jpg', 'foo.jpg') &&
            $mail->hasAttachmentFromStorage('/path/bar.jpg', 'bar.jpg');
    });

    /** @var Log */
    $log = Log::first();

    expect($log->status)->toBe(LogStatus::Sent);
    expect($log->error)->toBeNull();
    expect($log->attachments()->count())->toBe(2);
    expect($log->attachments()->pluck('name')->all())->toEqualCanonicalizing([
        'foo.jpg',
        'bar.jpg',
    ]);
});

it('sends mail with both regular and remote attachments', function () {
    Config::set('mailcarrier.attachments.log_strategy', AttachmentLogStrategy::None);

    MailCarrier::partialMock()
        ->shouldReceive('getFileSize')
        ->once()
        ->andReturn(1000);

    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    SendMail::resolve()->run(new SendMailDto([
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
        'attachments' => [
            $attachment = UploadedFile::fake()->image('foo.jpg'),
        ],
        'remoteAttachments' => [
            new RemoteAttachmentDto([
                'resource' => '/path/bar.jpg',
                'name' => 'bar.jpg',
            ]),
        ],
    ]));

    Mail::assertSent(GenericMail::class, 1);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) use ($attachment) {
        $mail->build();

        return $mail->hasTo('recipient@example.org') &&
            $mail->hasSubject('Welcome!') &&
            $mail->hasAttachedData($attachment->getContent(), 'foo.jpg') &&
            $mail->hasAttachmentFromStorage('/path/bar.jpg', 'bar.jpg');
    });

    /** @var Log */
    $log = Log::first();

    expect($log->status)->toBe(LogStatus::Sent);
    expect($log->error)->toBeNull();
    expect($log->attachments()->count())->toBe(2);
    expect($log->attachments()->pluck('name')->all())->toEqualCanonicalizing([
        'foo.jpg',
        'bar.jpg',
    ]);
});

it('sends mail to multiple recipients with recipient-defined regular attachments', function () {
    Config::set('mailcarrier.attachments.log_strategy', AttachmentLogStrategy::None);

    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    SendMail::resolve()->run(new SendMailDto([
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipients' => [
            [
                'email' => 'recipient1@example.org',
                'attachments' => [
                    $attachment1 = UploadedFile::fake()->image('foo.jpg'),
                ],
            ],
            [
                'email' => 'recipient2@example.org',
                'attachments' => [
                    $attachment2 = UploadedFile::fake()->image('bar.jpg'),
                ],
            ],
        ],
    ]));

    Mail::assertSent(GenericMail::class, 2);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) use ($attachment1) {
        $mail->build();

        return $mail->hasTo('recipient1@example.org') &&
            $mail->hasSubject('Welcome!') &&
            count($mail->rawAttachments) === 1 &&
            $mail->hasAttachedData($attachment1->getContent(), 'foo.jpg');
    });

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) use ($attachment2) {
        $mail->build();

        return $mail->hasTo('recipient2@example.org') &&
            $mail->hasSubject('Welcome!') &&
            count($mail->rawAttachments) === 1 &&
            $mail->hasAttachedData($attachment2->getContent(), 'bar.jpg');
    });

    /** @var Log */
    $log1 = Log::firstWhere('recipient', 'recipient1@example.org');

    /** @var Log */
    $log2 = Log::firstWhere('recipient', 'recipient2@example.org');

    expect($log1->status)->toBe(LogStatus::Sent);
    expect($log1->error)->toBeNull();
    expect($log1->attachments()->count())->toBe(1);
    expect($log1->attachments[0]->name)->toBe('foo.jpg');

    expect($log2->status)->toBe(LogStatus::Sent);
    expect($log2->error)->toBeNull();
    expect($log2->attachments()->count())->toBe(1);
    expect($log2->attachments[0]->name)->toBe('bar.jpg');
});

it('sends mail to multiple recipients with recipient-defined remote attachments', function () {
    Config::set('mailcarrier.attachments.log_strategy', AttachmentLogStrategy::None);

    MailCarrier::partialMock()
        ->shouldReceive('getFileSize')
        ->twice()
        ->andReturn(1000);

    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    SendMail::resolve()->run(new SendMailDto([
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipients' => [
            [
                'email' => 'recipient1@example.org',
                'remoteAttachments' => [
                    new RemoteAttachmentDto([
                        'resource' => '/path/foo.jpg',
                        'name' => 'foo.jpg',
                    ]),
                ],
            ],
            [
                'email' => 'recipient2@example.org',
                'remoteAttachments' => [
                    new RemoteAttachmentDto([
                        'resource' => '/path/bar.jpg',
                        'name' => 'bar.jpg',
                    ]),
                ],
            ],
        ],
    ]));

    Mail::assertSent(GenericMail::class, 2);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient1@example.org') &&
            $mail->hasSubject('Welcome!') &&
            count($mail->diskAttachments) === 1 &&
            $mail->hasAttachmentFromStorage('/path/foo.jpg', 'foo.jpg');
    });

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient2@example.org') &&
            $mail->hasSubject('Welcome!') &&
            count($mail->diskAttachments) === 1 &&
            $mail->hasAttachmentFromStorage('/path/bar.jpg', 'bar.jpg');
    });

    /** @var Log */
    $log1 = Log::firstWhere('recipient', 'recipient1@example.org');

    /** @var Log */
    $log2 = Log::firstWhere('recipient', 'recipient2@example.org');

    expect($log1->status)->toBe(LogStatus::Sent);
    expect($log1->error)->toBeNull();
    expect($log1->attachments()->count())->toBe(1);
    expect($log1->attachments[0]->name)->toBe('foo.jpg');

    expect($log2->status)->toBe(LogStatus::Sent);
    expect($log2->error)->toBeNull();
    expect($log2->attachments()->count())->toBe(1);
    expect($log2->attachments[0]->name)->toBe('bar.jpg');
});

it('sends mail to multiple recipients with recipient-defined regular and remote attachments', function () {
    Config::set('mailcarrier.attachments.log_strategy', AttachmentLogStrategy::None);

    MailCarrier::partialMock()
        ->shouldReceive('getFileSize')
        ->twice()
        ->andReturn(1000);

    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    SendMail::resolve()->run(new SendMailDto([
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipients' => [
            [
                'email' => 'recipient1@example.org',
                'attachments' => [
                    $attachment1 = UploadedFile::fake()->image('foo1.jpg'),
                ],
                'remoteAttachments' => [
                    new RemoteAttachmentDto([
                        'resource' => '/path/foo2.jpg',
                        'name' => 'foo2.jpg',
                    ]),
                ],
            ],
            [
                'email' => 'recipient2@example.org',
                'attachments' => [
                    $attachment2 = UploadedFile::fake()->image('bar1.jpg'),
                ],
                'remoteAttachments' => [
                    new RemoteAttachmentDto([
                        'resource' => '/path/bar2.jpg',
                        'name' => 'bar2.jpg',
                    ]),
                ],
            ],
        ],
    ]));

    Mail::assertSent(GenericMail::class, 2);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) use ($attachment1) {
        $mail->build();

        return $mail->hasTo('recipient1@example.org') &&
            $mail->hasSubject('Welcome!') &&
            count($mail->rawAttachments) === 1 &&
            $mail->hasAttachedData($attachment1->getContent(), 'foo1.jpg') &&
            count($mail->diskAttachments) === 1 &&
            $mail->hasAttachmentFromStorage('/path/foo2.jpg', 'foo2.jpg');
    });

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) use ($attachment2) {
        $mail->build();

        return $mail->hasTo('recipient2@example.org') &&
            $mail->hasSubject('Welcome!') &&
            count($mail->rawAttachments) === 1 &&
            $mail->hasAttachedData($attachment2->getContent(), 'bar1.jpg') &&
            count($mail->diskAttachments) === 1 &&
            $mail->hasAttachmentFromStorage('/path/bar2.jpg', 'bar2.jpg');
    });

    /** @var Log */
    $log1 = Log::firstWhere('recipient', 'recipient1@example.org');

    /** @var Log */
    $log2 = Log::firstWhere('recipient', 'recipient2@example.org');

    expect($log1->status)->toBe(LogStatus::Sent);
    expect($log1->error)->toBeNull();
    expect($log1->attachments()->count())->toBe(2);
    expect($log1->attachments()->pluck('name')->all())->toEqualCanonicalizing([
        'foo1.jpg',
        'foo2.jpg',
    ]);

    expect($log2->status)->toBe(LogStatus::Sent);
    expect($log2->error)->toBeNull();
    expect($log2->attachments()->count())->toBe(2);
    expect($log2->attachments()->pluck('name')->all())->toEqualCanonicalizing([
        'bar1.jpg',
        'bar2.jpg',
    ]);
});

it('merges the recipient-defined attachments with the request ones when there are multiple recipients', function () {
    Config::set('mailcarrier.attachments.log_strategy', AttachmentLogStrategy::None);

    MailCarrier::partialMock()
        ->shouldReceive('getFileSize')
        ->times(3)
        ->andReturn(1000);

    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    SendMail::resolve()->run(new SendMailDto([
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'attachments' => [
            $globalAttachment = UploadedFile::fake()->image('globalRaw.jpg'),
        ],
        'remoteAttachments' => [
            new RemoteAttachmentDto([
                'resource' => '/path/globalRemote.jpg',
                'name' => 'globalRemote.jpg',
            ]),
        ],
        'recipients' => [
            [
                'email' => 'recipient1@example.org',
                'remoteAttachments' => [
                    new RemoteAttachmentDto([
                        'resource' => '/path/foo.jpg',
                        'name' => 'foo.jpg',
                    ]),
                ],
            ],
            [
                'email' => 'recipient2@example.org',
                'attachments' => [
                    $recipientAttachment = UploadedFile::fake()->image('bar.jpg'),
                ],
            ],
        ],
    ]));

    Mail::assertSent(GenericMail::class, 2);

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) use ($globalAttachment) {
        $mail->build();

        return $mail->hasTo('recipient1@example.org') &&
            $mail->hasSubject('Welcome!') &&
            count($mail->rawAttachments) === 1 &&
            $mail->hasAttachedData($globalAttachment->getContent(), 'globalRaw.jpg') &&
            count($mail->diskAttachments) === 2 &&
            $mail->hasAttachmentFromStorage('/path/globalRemote.jpg', 'globalRemote.jpg') &&
            $mail->hasAttachmentFromStorage('/path/foo.jpg', 'foo.jpg');
    });

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) use ($globalAttachment, $recipientAttachment) {
        $mail->build();

        return $mail->hasTo('recipient2@example.org') &&
            $mail->hasSubject('Welcome!') &&
            count($mail->rawAttachments) === 2 &&
            $mail->hasAttachedData($globalAttachment->getContent(), 'globalRaw.jpg') &&
            $mail->hasAttachedData($recipientAttachment->getContent(), 'bar.jpg') &&
            count($mail->diskAttachments) === 1 &&
            $mail->hasAttachmentFromStorage('/path/globalRemote.jpg', 'globalRemote.jpg');
    });

    /** @var Log */
    $log1 = Log::firstWhere('recipient', 'recipient1@example.org');

    /** @var Log */
    $log2 = Log::firstWhere('recipient', 'recipient2@example.org');

    expect($log1->status)->toBe(LogStatus::Sent);
    expect($log1->error)->toBeNull();
    expect($log1->attachments()->count())->toBe(3);
    expect($log1->attachments()->pluck('name')->all())->toEqualCanonicalizing([
        'globalRaw.jpg',
        'globalRemote.jpg',
        'foo.jpg',
    ]);

    expect($log2->status)->toBe(LogStatus::Sent);
    expect($log2->error)->toBeNull();
    expect($log2->attachments()->count())->toBe(3);
    expect($log2->attachments()->pluck('name')->all())->toEqualCanonicalizing([
        'globalRaw.jpg',
        'globalRemote.jpg',
        'bar.jpg',
    ]);
});

it('invokes the before sending middleware', function () {
    MailCarrier::beforeSending(function (): void {
        throw new \Exception('beforeSending middleware');
    });

    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    SendMail::resolve()->run(new SendMailDto([
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
    ]));

    Mail::assertNothingOutgoing();
})->throws(\Exception::class, 'beforeSending middleware');

it('invokes the sending middleware', function () {
    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    MailCarrier::sending(function (GenericMailDto $mail, \Closure $next): void {
        $next();

        // Throw after sending mail
        throw new \Exception('sending after middleware');
    });

    $call = fn () => SendMail::resolve()->run(new SendMailDto([
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
    ]));

    expect($call)->toThrow(\Exception::class, 'sending after middleware');

    Mail::assertSent(GenericMail::class, 1);
    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient@example.org')
            && $mail->hasSubject('Welcome!');
    });
});

it('allows to override the GenericMailDto from the sending middleware', function () {
    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    SendMail::resolve()->run(new SendMailDto([
        'enqueue' => false,
        'sender' => 'sender@example.org',
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
    ]));

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasFrom('sender@example.org')
            && $mail->hasTo('recipient@example.org')
            && $mail->hasSubject('Welcome!');
    });

    MailCarrier::sending(function (GenericMailDto $mail, \Closure $next): void {
        $next($mail->clone(
            sender: [
                'email' => 'new-sender@example.org',
            ],
        ));
    });

    SendMail::resolve()->run(new SendMailDto([
        'enqueue' => false,
        'sender' => 'sender@example.org',
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
    ]));

    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasFrom('new-sender@example.org')
            && $mail->hasTo('recipient@example.org')
            && $mail->hasSubject('Welcome!');
    });
});

it('accepts an array of tags', function () {
    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
        'tags' => ['foo', 'bar'],
    ])->assertOk();

    Mail::assertSent(GenericMail::class, 1);
    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient@example.org')
            && $mail->hasSubject('Welcome!')
            && $mail->hasTag('foo')
            && $mail->hasTag('bar');
    });
});

it('accepts an array of metadata', function () {
    Template::factory()->create([
        'slug' => 'welcome',
    ]);

    postJson(route('mailcarrier.send'), [
        'enqueue' => false,
        'template' => 'welcome',
        'subject' => 'Welcome!',
        'recipient' => 'recipient@example.org',
        'metadata' => [
            'meta1' => 'value1',
            'meta2' => 'value2',
        ],
    ])->assertOk();

    Mail::assertSent(GenericMail::class, 1);
    Mail::assertSent(GenericMail::class, function (GenericMail $mail) {
        $mail->build();

        return $mail->hasTo('recipient@example.org')
            && $mail->hasSubject('Welcome!')
            && $mail->hasMetadata('meta1', 'value1')
            && $mail->hasMetadata('meta2', 'value2');
    });
});
