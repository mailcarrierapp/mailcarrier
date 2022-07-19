<?php

namespace MailCarrier\MailCarrier\Dto;

use MailCarrier\MailCarrier\Dto\Casters\ContactStringCaster;
use MailCarrier\MailCarrier\Dto\Validators\Email;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Casters\ArrayCaster;
use Spatie\DataTransferObject\DataTransferObject;

class RecipientDto extends DataTransferObject
{
    #[Email]
    public string $recipient;

    /** @var array<string, mixed> */
    public array $variables = [];

    #[CastWith(ContactStringCaster::class)]
    public ?ContactDto $cc;

    #[CastWith(ContactStringCaster::class)]
    public ?ContactDto $bcc;

    /** @var \Illuminate\Http\UploadedFile[] */
    public array $attachments = [];

    /** @var \MailCarrier\MailCarrier\Dto\RemoteAttachmentDto[] */
    #[CastWith(ArrayCaster::class, itemType: RemoteAttachmentDto::class)]
    public array $remoteAttachments = [];
}
