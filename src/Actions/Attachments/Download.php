<?php

namespace MailCarrier\MailCarrier\Actions\Attachments;

use MailCarrier\MailCarrier\Actions\Action;
use MailCarrier\MailCarrier\Enums\AttachmentLogStrategy;
use MailCarrier\MailCarrier\Exceptions\AttachmentNotDownloadableException;
use MailCarrier\MailCarrier\Exceptions\AttachmentNotFoundException;
use MailCarrier\MailCarrier\Http\GenericFile;
use MailCarrier\MailCarrier\MailCarrier;
use MailCarrier\MailCarrier\Models\Attachment;

class Download extends Action
{
    /**
     * Download an attachment.
     *
     * @throws \MailCarrier\MailCarrier\Exceptions\AttachmentNotFoundException
     * @throws \MailCarrier\MailCarrier\Exceptions\AttachmentNotDownloadableException
     */
    public function run(Attachment $attachment): ?GenericFile
    {
        if ($attachment->strategy === AttachmentLogStrategy::Upload) {
            if (! MailCarrier::storage($attachment->disk)->exists($attachment->path)) {
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
