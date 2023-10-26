<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use MailCarrier\Actions\Logs\CreateFromGenericMail;
use MailCarrier\Dto\AttachmentDto;
use MailCarrier\Dto\ContactDto;
use MailCarrier\Dto\GenericMailDto;
use MailCarrier\Dto\RemoteAttachmentDto;
use MailCarrier\Enums\AttachmentLogStrategy;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Facades\MailCarrier;
use MailCarrier\Models\Attachment;
use MailCarrier\Models\Log;
use MailCarrier\Models\Template;
use function Pest\Laravel\assertDatabaseCount;

it('sets the correct status when the log has errors', function (?string $error, LogStatus $expected) {
    assertDatabaseCount(Log::class, 0);

    $template = Template::factory()->create();

    $log = CreateFromGenericMail::resolve()->run(new GenericMailDto(
        trigger: 'test',
        content: 'body',
        subject: 'Welcome',
        error: $error,
        recipient: 'foo@example.org',
        sender: new ContactDto(
            email: 'sender@example.org',
        ),
        cc: [
            new ContactDto(
                email: 'cc@example.org',
            ),
        ],
        bcc: [
            new ContactDto(
                email: 'bcc@example.org',
            ),
            new ContactDto(
                email: 'bcc2@example.org',
            ),
        ],
        template: $template,
        variables: ['name' => 'foo'],
    ));

    expect($log->trigger)->toBe('test');
    expect($log->subject)->toBe('Welcome');
    expect($log->sender->email)->toBe('sender@example.org');
    expect($log->cc[0]->email)->toBe('cc@example.org');
    expect($log->bcc[0]->email)->toBe('bcc@example.org');
    expect($log->bcc[1]->email)->toBe('bcc2@example.org');
    expect($log->variables)->tobe(['name' => 'foo']);
    expect($log->template->id)->toBe($template->id);
    expect($log->status)->toBe($expected);
})->with([
    'no errors' => [null, LogStatus::Pending],
    'error' => ['error', LogStatus::Failed],
]);

it('fallbacks to default config sender if no sender is specified', function () {
    Config::set('mail.from', [
        'name' => 'MailCarrier',
        'address' => 'no-reply@mailcarrier.app',
    ]);

    assertDatabaseCount(Log::class, 0);

    $template = Template::factory()->create();

    $genericMail = new GenericMailDto(
        trigger: 'test',
        content: 'body',
        subject: 'Welcome',
        error: null,
        recipient: 'foo@example.org',
        cc: [
            new ContactDto(
                email: 'cc@example.org',
            ),
        ],
        bcc: [
            new ContactDto(
                email: 'bcc@example.org',
            ),
        ],
        template: $template,
        variables: ['name' => 'foo'],
    );

    $log = CreateFromGenericMail::resolve()->run($genericMail);

    expect($genericMail->sender)->toBeNull();

    expect($log->trigger)->toBe('test');
    expect($log->subject)->toBe('Welcome');
    expect($log->sender->name)->toBe('MailCarrier');
    expect($log->sender->email)->toBe('no-reply@mailcarrier.app');
    expect($log->cc->email)->toBe('cc@example.org');
    expect($log->bcc->email)->toBe('bcc@example.org');
    expect($log->variables)->tobe(['name' => 'foo']);
    expect($log->template->id)->toBe($template->id);
    expect($log->status)->toBe(LogStatus::Pending);
});

it('freezes the template data', function () {
    assertDatabaseCount(Log::class, 0);

    $template = Template::factory()->create([
        'name' => 'template',
        'content' => 'hello {{ name }}',
    ]);

    $log = CreateFromGenericMail::resolve()->run(new GenericMailDto(
        trigger: 'test',
        content: 'hello foo',
        subject: 'Welcome',
        error: null,
        recipient: 'foo@example.org',
        sender: new ContactDto(
            email: 'sender@example.org',
        ),
        cc: [
            new ContactDto(
                email: 'cc@example.org',
            ),
        ],
        bcc: [
            new ContactDto(
                email: 'bcc@example.org',
            ),
        ],
        template: $template,
        variables: ['name' => 'foo'],
    ));

    expect($log->trigger)->toBe('test');
    expect($log->subject)->toBe('Welcome');
    expect($log->sender->email)->toBe('sender@example.org');
    expect($log->cc->email)->toBe('cc@example.org');
    expect($log->bcc->email)->toBe('bcc@example.org');
    expect($log->variables)->tobe(['name' => 'foo']);
    expect($log->template->id)->toBe($template->id);
    expect($log->template_frozen->name)->toBe('template');
    expect($log->template_frozen->render)->toBe('hello foo');
    expect($log->status)->toBe(LogStatus::Pending);
});

it('creates an attachment with attachment strategy NONE', function () {
    Config::set('mailcarrier.attachments.log_strategy', AttachmentLogStrategy::None);
    Config::set('mailcarrier.attachments.disk', 'default_disk');

    assertDatabaseCount(Log::class, 0);
    assertDatabaseCount(Attachment::class, 0);

    $genericMail = new GenericMailDto(
        trigger: 'test',
        content: 'hello foo',
        subject: 'Welcome',
        error: null,
        recipient: 'foo@example.org',
        sender: new ContactDto(
            email: 'sender@example.org',
        ),
        cc: [
            new ContactDto(
                email: 'cc@example.org',
            ),
        ],
        bcc: [
            new ContactDto(
                email: 'bcc@example.org',
            ),
        ],
        template: Template::factory()->create(),
        variables: ['name' => 'foo'],
        attachments: [
            new AttachmentDto(UploadedFile::fake()->create('image.jpg', 100, 'image/jpeg')),
        ],
        remoteAttachments: [
            new RemoteAttachmentDto(
                resource: '/attachment/one',
                name: 'contract.pdf',
                disk: 's3',
            ),
            new RemoteAttachmentDto(
                resource: '/attachment/two',
                name: 'house.pdf',
                disk: null,
            ),
        ],
    );

    MailCarrier::partialMock()
        ->shouldNotReceive('upload');

    MailCarrier::partialMock()
        ->shouldNotReceive('download');

    MailCarrier::partialMock()
        ->shouldReceive('getFileSize')
        ->once()
        ->with($genericMail->remoteAttachments[0]->resource, $genericMail->remoteAttachments[0]->disk)
        ->andReturn(200 * 1024);

    MailCarrier::partialMock()
        ->shouldReceive('getFileSize')
        ->once()
        ->with($genericMail->remoteAttachments[1]->resource, $genericMail->remoteAttachments[1]->disk)
        ->andReturn(300 * 1024);

    $log = CreateFromGenericMail::resolve()->run($genericMail);
    $logAttachments = $log->attachments()->get();

    /** @var Attachment */
    $standardAttachment = $logAttachments[0];

    /** @var Attachment */
    $remoteAttachment = $logAttachments[1];

    /** @var Attachment */
    $remoteAttachment2 = $logAttachments[2];

    expect($logAttachments->count())->toBe(3);

    expect($standardAttachment->strategy)->toBe(AttachmentLogStrategy::None);
    expect($standardAttachment->name)->toBe('image.jpg');
    expect($standardAttachment->size)->toBe(100 * 1024);
    expect($standardAttachment->content)->toBeNull();
    expect($standardAttachment->path)->toBeNull(); // No path for non-uploading strategy
    expect($standardAttachment->disk)->toBeNull(); // No disk for standard attachments

    expect($remoteAttachment->strategy)->toBe(AttachmentLogStrategy::None);
    expect($remoteAttachment->name)->toBe('contract.pdf');
    expect($remoteAttachment->size)->toBe(200 * 1024);
    expect($remoteAttachment->content)->toBeNull();
    expect($remoteAttachment->path)->toBeNull(); // No path for non-uploading strategy
    expect($remoteAttachment->disk)->toBe('s3');

    expect($remoteAttachment2->strategy)->toBe(AttachmentLogStrategy::None);
    expect($remoteAttachment2->name)->toBe('house.pdf');
    expect($remoteAttachment2->size)->toBe(300 * 1024);
    expect($remoteAttachment2->content)->toBeNull();
    expect($remoteAttachment2->path)->toBeNull(); // No path for non-uploading strategy
    expect($remoteAttachment2->disk)->toBe('default_disk'); // Fallback to default disk
});

it('creates an attachment with attachment strategy INLINE', function () {
    Config::set('mailcarrier.attachments.log_strategy', AttachmentLogStrategy::Inline);
    Config::set('mailcarrier.attachments.disk', 'default_disk');

    assertDatabaseCount(Log::class, 0);
    assertDatabaseCount(Attachment::class, 0);

    $genericMail = new GenericMailDto(
        trigger: 'test',
        content: 'hello foo',
        subject: 'Welcome',
        error: null,
        recipient: 'foo@example.org',
        sender: new ContactDto(
            email: 'sender@example.org',
        ),
        cc: [
            new ContactDto(
                email: 'cc@example.org',
            ),
        ],
        bcc: [
            new ContactDto(
                email: 'bcc@example.org',
            ),
        ],
        template: Template::factory()->create(),
        variables: ['name' => 'foo'],
        attachments: [
            new AttachmentDto(UploadedFile::fake()->create('image.jpg', 100, 'image/jpeg')),
        ],
        remoteAttachments: [
            new RemoteAttachmentDto(
                resource: '/attachment/one',
                name: 'contract.pdf',
                disk: 's3',
            ),
            new RemoteAttachmentDto(
                resource: '/attachment/two',
                name: 'house.pdf',
                disk: null,
            ),
        ],
    );

    MailCarrier::partialMock()
        ->shouldNotReceive('upload');

    MailCarrier::partialMock()
        ->shouldReceive('download')
        ->once()
        ->with($genericMail->remoteAttachments[0]->resource, $genericMail->remoteAttachments[0]->disk)
        ->andReturn('foo');

    MailCarrier::partialMock()
        ->shouldReceive('download')
        ->once()
        ->with($genericMail->remoteAttachments[1]->resource, $genericMail->remoteAttachments[1]->disk)
        ->andReturn(null);

    MailCarrier::partialMock()
        ->shouldReceive('getFileSize')
        ->once()
        ->with($genericMail->remoteAttachments[0]->resource, $genericMail->remoteAttachments[0]->disk)
        ->andReturn(200 * 1024);

    MailCarrier::partialMock()
        ->shouldReceive('getFileSize')
        ->once()
        ->with($genericMail->remoteAttachments[1]->resource, $genericMail->remoteAttachments[1]->disk)
        ->andReturn(300 * 1024);

    $log = CreateFromGenericMail::resolve()->run($genericMail);
    $logAttachments = $log->attachments()->get();

    /** @var Attachment */
    $standardAttachment = $logAttachments[0];

    /** @var Attachment */
    $remoteAttachment = $logAttachments[1];

    /** @var Attachment */
    $remoteAttachment2 = $logAttachments[2];

    expect($logAttachments->count())->toBe(3);

    expect($standardAttachment->strategy)->toBe(AttachmentLogStrategy::Inline);
    expect($standardAttachment->name)->toBe('image.jpg');
    expect($standardAttachment->size)->toBe(100 * 1024);
    expect($standardAttachment->content)->not->toBeNull();
    expect($standardAttachment->path)->toBeNull(); // No path for non-uploading strategy
    expect($standardAttachment->disk)->toBeNull(); // No disk for standard attachments

    expect($remoteAttachment->strategy)->toBe(AttachmentLogStrategy::Inline);
    expect($remoteAttachment->name)->toBe('contract.pdf');
    expect($remoteAttachment->size)->toBe(200 * 1024);
    expect($remoteAttachment->getRawOriginal('content'))->not->toBeNull()->not->toBe('foo'); // It's encrypted
    expect($remoteAttachment->content)->toBe('foo'); // We have the result here because of cast decrypting it
    expect($remoteAttachment->path)->toBeNull(); // No path for non-uploading strategy
    expect($remoteAttachment->disk)->toBe('s3');

    expect($remoteAttachment2->strategy)->toBe(AttachmentLogStrategy::Inline);
    expect($remoteAttachment2->name)->toBe('house.pdf');
    expect($remoteAttachment2->size)->toBe(300 * 1024);
    expect($remoteAttachment2->content)->toBeNull();
    expect($remoteAttachment2->path)->toBeNull(); // No path for non-uploading strategy
    expect($remoteAttachment2->disk)->toBe('default_disk'); // Fallback to default disk
});

it('creates an attachment with attachment strategy UPLOAD', function () {
    Config::set('mailcarrier.attachments.log_strategy', AttachmentLogStrategy::Upload);
    Config::set('mailcarrier.attachments.disk', 'default_disk');

    assertDatabaseCount(Log::class, 0);
    assertDatabaseCount(Attachment::class, 0);

    $genericMail = new GenericMailDto(
        trigger: 'test',
        content: 'hello foo',
        subject: 'Welcome',
        error: null,
        recipient: 'foo@example.org',
        sender: new ContactDto(
            email: 'sender@example.org',
        ),
        cc: [
            new ContactDto(
                email: 'cc@example.org',
            ),
        ],
        bcc: [
            new ContactDto(
                email: 'bcc@example.org',
            ),
        ],
        template: Template::factory()->create(),
        variables: ['name' => 'foo'],
        attachments: [
            new AttachmentDto(UploadedFile::fake()->create('image.jpg', 100, 'image/jpeg')),
        ],
        remoteAttachments: [
            new RemoteAttachmentDto(
                resource: '/attachment/one',
                name: 'contract.pdf',
                disk: 's3',
            ),
            new RemoteAttachmentDto(
                resource: '/attachment/two',
                name: 'house.pdf',
                disk: null,
            ),
        ],
    );

    MailCarrier::partialMock()
        ->shouldReceive('upload')
        ->once()
        ->withArgs(fn (string $content, string $name) => $name === 'image.jpg')
        ->andReturn('/attachment/standard');

    MailCarrier::partialMock()
        ->shouldNotReceive('download');

    MailCarrier::partialMock()
        ->shouldReceive('getFileSize')
        ->once()
        ->with($genericMail->remoteAttachments[0]->resource, $genericMail->remoteAttachments[0]->disk)
        ->andReturn(200 * 1024);

    MailCarrier::partialMock()
        ->shouldReceive('getFileSize')
        ->once()
        ->with($genericMail->remoteAttachments[1]->resource, $genericMail->remoteAttachments[1]->disk)
        ->andReturn(300 * 1024);

    $log = CreateFromGenericMail::resolve()->run($genericMail);
    $logAttachments = $log->attachments()->get();

    /** @var Attachment */
    $standardAttachment = $logAttachments[0];

    /** @var Attachment */
    $remoteAttachment = $logAttachments[1];

    /** @var Attachment */
    $remoteAttachment2 = $logAttachments[2];

    expect($logAttachments->count())->toBe(3);

    expect($standardAttachment->strategy)->toBe(AttachmentLogStrategy::Upload);
    expect($standardAttachment->name)->toBe('image.jpg');
    expect($standardAttachment->size)->toBe(100 * 1024);
    expect($standardAttachment->content)->toBeNull();
    expect($standardAttachment->path)->toBe('/attachment/standard');
    expect($standardAttachment->disk)->toBe('default_disk');

    expect($remoteAttachment->strategy)->toBe(AttachmentLogStrategy::Upload);
    expect($remoteAttachment->name)->toBe('contract.pdf');
    expect($remoteAttachment->size)->toBe(200 * 1024);
    expect($remoteAttachment->content)->toBeNull();
    expect($remoteAttachment->path)->toBe('/attachment/one');
    expect($remoteAttachment->disk)->toBe('s3');

    expect($remoteAttachment2->strategy)->toBe(AttachmentLogStrategy::Upload);
    expect($remoteAttachment2->name)->toBe('house.pdf');
    expect($remoteAttachment2->size)->toBe(300 * 1024);
    expect($remoteAttachment2->content)->toBeNull();
    expect($remoteAttachment2->path)->toBe('/attachment/two');
    expect($remoteAttachment2->disk)->toBe('default_disk'); // Fallback to default disk
});
