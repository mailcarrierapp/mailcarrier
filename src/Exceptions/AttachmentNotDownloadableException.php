<?php

namespace MailCarrier\Exceptions;

use MailCarrier\Enums\ApiErrorKey;
use MailCarrier\Models\Attachment;

class AttachmentNotDownloadableException extends MailCarrierException
{
    public function __construct(protected Attachment $attachment)
    {
        parent::__construct(
            sprintf(
                'The attachment "%s" cannot be download due to its strategy.',
                $attachment->name,
            )
        );
    }

    public function getErrorKey(): ApiErrorKey
    {
        return ApiErrorKey::AttachmentNotDownloadable;
    }
}
