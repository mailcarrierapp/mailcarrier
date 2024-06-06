<?php

namespace MailCarrier\Actions\Logs;

use Illuminate\Support\Collection;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use MailCarrier\Actions\Action;
use MailCarrier\Actions\SendMail;
use MailCarrier\Dto\AttachmentDto;
use MailCarrier\Dto\SendMailDto;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Models\Attachment;
use MailCarrier\Models\Log;

class ResendEmail extends Action
{
    public function run(Log $log, array $data = []): void
    {
        $attachments = $data['attachments'] ?? Collection::make([]);

        // Update the status to Failed just to send email again
        if ($log->isSent()) {
            $log->update([
                'status' => LogStatus::Failed,
            ]);
        }

        SendMail::resolve()->run(
            new SendMailDto([
                'template' => $log->template->slug,
                'subject' => $log->subject,
                'sender' => $log->sender,
                'recipient' => $log->recipient,
                'cc' => $log->cc->all(),
                'bcc' => $log->bcc->all(),
                'variables' => $log->variables,
                'trigger' => $log->trigger,
                'tags' => $log->tags ?: [],
                'metadata' => $log->metadata ?: [],
                'attachments' => $log->attachments
                    ->map(
                        fn (Attachment $attachment) => !$attachment->canBeDownloaded()
                            ? null
                            : new AttachmentDto(
                                name: $attachment->name,
                                content: $attachment->content,
                                size: $attachment->size
                            )
                    )
                    ->filter()
                    ->merge(
                        Collection::make($attachments)->map(
                            fn (TemporaryUploadedFile $file) => new AttachmentDto(
                                name: $file->getClientOriginalName(),
                                content: base64_encode(file_get_contents($file->getRealPath())),
                                size: $file->getSize()
                            )
                        )
                    )
                    ->all(),
            ]),
            $log
        );
    }
}
