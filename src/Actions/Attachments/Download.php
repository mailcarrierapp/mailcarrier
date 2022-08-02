<?php

namespace MailCarrier\Actions\Attachments;

use MailCarrier\Actions\Action;
use MailCarrier\Enums\AttachmentLogStrategy;
use MailCarrier\Exceptions\AttachmentNotDownloadableException;
use MailCarrier\Exceptions\AttachmentNotFoundException;
use MailCarrier\Facades\MailCarrier;
use MailCarrier\Http\GenericFile;
use MailCarrier\Models\Attachment;

class Download extends Action
{
    /**
     * Download an attachment.
     *
     * @throws \MailCarrier\Exceptions\AttachmentNotFoundException
     * @throws \MailCarrier\Exceptions\AttachmentNotDownloadableException
     */
    public function run(Attachment $attachment): ?GenericFile
    {
        if ($attachment->strategy === AttachmentLogStrategy::Upload) {
            if (!MailCarrier::storage($attachment->disk)->exists($attachment->path)) {
                throw new AttachmentNotFoundException($attachment);
            }
        }

        return match ($attachment->strategy) {
            AttachmentLogStrategy::None => throw new AttachmentNotDownloadableException($attachment),
            AttachmentLogStrategy::Inline => new GenericFile(base64_decode($attachment->content), $attachment->name),
            AttachmentLogStrategy::Upload => new GenericFile(
                base64_decode(MailCarrier::download($attachment->path, $attachment->disk)),
                $attachment->name
            ),
        };
    }
}
