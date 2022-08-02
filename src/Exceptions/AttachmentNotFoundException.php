<?php

namespace MailCarrier\Exceptions;

use Illuminate\Support\Facades\Config;
use MailCarrier\Enums\ApiErrorKey;
use MailCarrier\Models\Attachment;

class AttachmentNotFoundException extends MailCarrierException
{
    public function __construct(protected Attachment $attachment)
    {
        parent::__construct(
            sprintf(
                'The attachment "%s" has not been found on the disk "%s".',
                $attachment->path,
                $attachment->disk ?: Config::get('mailcarrier.attachments.disk'),
            )
        );
    }

    public function getErrorKey(): ApiErrorKey
    {
        return ApiErrorKey::AttachmentNotFound;
    }
}
