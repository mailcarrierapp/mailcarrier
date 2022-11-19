<?php

use MailCarrier\Actions\Attachments\Download;
use MailCarrier\Enums\AttachmentLogStrategy;
use MailCarrier\Exceptions\AttachmentNotDownloadableException;
use MailCarrier\Exceptions\AttachmentNotFoundException;
use MailCarrier\Facades\MailCarrier;
use MailCarrier\Models\Attachment;

it('throws exception when strategy is Upload and file does not exist on disk', function () {
    $attachment = Attachment::factory()->create([
        'strategy' => AttachmentLogStrategy::Upload,
    ]);

    MailCarrier::shouldReceive('fileExists')
        ->andReturn(false);

    Download::resolve()->run($attachment);
})->throws(AttachmentNotFoundException::class);

it('throws exception when strategy is None', function () {
    $attachment = Attachment::factory()->create([
        'strategy' => AttachmentLogStrategy::None,
    ]);

    Download::resolve()->run($attachment);
})->throws(AttachmentNotDownloadableException::class);

it('returns file content from db when strategy is Inline', function () {
    $attachment = Attachment::factory()->create([
        'strategy' => AttachmentLogStrategy::Inline,
        'name' => 'file.pdf',
        'content' => base64_encode('foo'),
    ]);

    /** @var \MailCarrier\Http\GenericFile */
    $result = Download::resolve()->run($attachment);

    expect($result->fileName)->toBe('file.pdf');
    expect($result->content)->toBe('foo');
});

it('returns file content from disk when strategy is Upload', function () {
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

    /** @var \MailCarrier\Http\GenericFile */
    $result = Download::resolve()->run($attachment);

    expect($result->fileName)->toBe('file.pdf');
    expect($result->content)->toBe('foo');
});
