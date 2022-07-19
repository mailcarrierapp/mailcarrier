<?php

namespace MailCarrier\MailCarrier\Exceptions;

use MailCarrier\MailCarrier\Enums\ApiErrorKey;
use MailCarrier\MailCarrier\Models\Attachment;

class AttachmentNotDownloadableException extends \Exception
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
