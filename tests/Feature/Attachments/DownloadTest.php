<?php

use Illuminate\Http\JsonResponse;
use MailCarrier\Enums\AttachmentLogStrategy;
use MailCarrier\Facades\MailCarrier;
use MailCarrier\Models\Attachment;
use function Pest\Faker\fake;
use function Pest\Laravel\getJson;

it('blocks guests', function () {
    getJson(route('download.attachment', fake()->uuid()))
        ->assertUnauthorized();
});

it('throws not found if the attachment does not exist', function () {
    actingAsUser()
        ->getJson(route('download.attachment', fake()->uuid()))
        ->assertNotFound();
});

it('throws error when strategy is Upload and file does not exist on disk', function () {
    $attachment = Attachment::factory()->create([
        'strategy' => AttachmentLogStrategy::Upload,
    ]);

    MailCarrier::shouldReceive('fileExists')
        ->andReturn(false);

    actingAsUser()
        ->getJson(route('download.attachment', $attachment))
        ->assertStatus(JsonResponse::HTTP_PRECONDITION_FAILED)
        ->assertJsonPath('key', 'ATTACHMENT_NOT_FOUND_ON_DISK');
});

it('throws error when strategy is None', function () {
    $attachment = Attachment::factory()->create([
        'strategy' => AttachmentLogStrategy::None,
    ]);

    actingAsUser()
        ->getJson(route('download.attachment', $attachment))
        ->assertStatus(JsonResponse::HTTP_PRECONDITION_FAILED)
        ->assertJsonPath('key', 'ATTACHMENT_NOT_DOWNLOADABLE');
});

it('downloads file from db when strategy is Inline', function () {
    $attachment = Attachment::factory()->create([
        'strategy' => AttachmentLogStrategy::Inline,
        'name' => 'file.pdf',
        'content' => base64_encode('foo'),
    ]);

    actingAsUser()
        ->getJson(route('download.attachment', $attachment))
        ->assertOk()
        ->assertDownload('file.pdf');
});

it('downloads file from disk when strategy is Upload', function () {
    $attachment = Attachment::factory()->create([
        'strategy' => AttachmentLogStrategy::Upload,
        'name' => 'file.pdf',
        'path' => 'foo/bar.pdf',
        'disk' => 'fake-disk',
        'content' => null,
    ]);

    MailCarrier::shouldReceive('fileExists')
        ->andReturn(true);

    MailCarrier::shouldReceive('download')
        ->with('foo/bar.pdf', 'fake-disk')
        ->andReturn(base64_encode('foo'));

    actingAsUser()
        ->getJson(route('download.attachment', $attachment))
        ->assertOk()
        ->assertDownload('file.pdf');
});
