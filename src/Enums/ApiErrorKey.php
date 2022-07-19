<?php

namespace MailCarrier\MailCarrier\Enums;

enum ApiErrorKey: string
{
    case UnexpectedError = 'UNEXPECTED_ERROR';
    case MissingVariable = 'MISSING_VARIABLE';
    case AttachmentNotDownloadable = 'ATTACHMENT_NOT_DOWNLOADABLE';
    case AttachmentNotFound = 'ATTACHMENT_NOT_FOUND_ON_DISK';
}
