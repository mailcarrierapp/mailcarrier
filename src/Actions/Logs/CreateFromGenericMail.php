<?php

namespace MailCarrier\MailCarrier\Actions\Logs;

use Illuminate\Support\Facades\Config;
use MailCarrier\MailCarrier\Actions\Action;
use MailCarrier\MailCarrier\Dto\AttachmentDto;
use MailCarrier\MailCarrier\Dto\GenericMailDto;
use MailCarrier\MailCarrier\Dto\LogTemplateDto;
use MailCarrier\MailCarrier\Dto\RemoteAttachmentDto;
use MailCarrier\MailCarrier\Enums\AttachmentLogStrategy;
use MailCarrier\MailCarrier\Enums\LogStatus;
use MailCarrier\MailCarrier\Facades\MailCarrier;
use MailCarrier\MailCarrier\Models\Log;

class CreateFromGenericMail extends Action
{
    /**
     * Insert multiple logs at once.
     */
    public function run(GenericMailDto $genericMailDto): Log
    {
        /** @var Log */
        $log = Log::query()->create([
            'template_id' => $genericMailDto->template->id,
            'status' => $genericMailDto->error ? LogStatus::Failed : LogStatus::Pending,
            'trigger' => $genericMailDto->trigger,
            'subject' => $genericMailDto->subject,
            'sender' => $genericMailDto->sender,
            'recipient' => $genericMailDto->recipient,
            'cc' => $genericMailDto->cc,
            'bcc' => $genericMailDto->bcc,
            'error' => $genericMailDto->error,
            'template_frozen' => new LogTemplateDto(
                name: $genericMailDto->template->name,
                render: $genericMailDto->content,
                hash: $genericMailDto->template->getHash(),
            ),
            'variables' => $genericMailDto->variables,
        ]);

        $log->attachments()->createMany(
            array_map(
                $this->buildAttachment(...),
                [
                    ...$genericMailDto->attachments,
                    ...$genericMailDto->remoteAttachments,
                ]
            )
        );

        GetTriggers::flush();

        return $log;
    }

    /**
     * Transform an attachment to be saved in the log.
     */
    protected function buildAttachment(AttachmentDto|RemoteAttachmentDto $attachment): array
    {
        $attachmentStrategy = Config::get('mailcarrier.attachments.log_strategy');
        $defaultDisk = Config::get('mailcarrier.attachments.disk');
        $isInline = $attachmentStrategy === AttachmentLogStrategy::Inline;
        $shouldBeUploaded = $attachmentStrategy === AttachmentLogStrategy::Upload;

        $name = $attachment instanceof AttachmentDto ? $attachment->name : $attachment->resource;
        $size = $attachment instanceof AttachmentDto ? $attachment->size : MailCarrier::getFileSize($attachment->resource, $attachment->disk);
        $content = $attachment instanceof AttachmentDto ? $attachment->content : null;
        $path = $attachment instanceof AttachmentDto ? null : $attachment->resource;
        $disk = $attachment instanceof AttachmentDto ? null : ($attachment->disk ?: $defaultDisk);

        if ($attachment instanceof AttachmentDto && $shouldBeUploaded) {
            $path = MailCarrier::upload($attachment->content, $attachment->name);
            $disk = $defaultDisk;
        }

        if ($attachment instanceof RemoteAttachmentDto && $isInline) {
            $content = MailCarrier::download($attachment->resource, $attachment->disk);
        }

        return [
            'strategy' => $attachmentStrategy,
            'name' => $name,
            'size' => $size,
            'content' => $isInline ? $content : null,
            'path' => $shouldBeUploaded ? $path : null,
            'disk' => $disk,
        ];
    }
}
