<?php

namespace MailCarrier\Dto;

use MailCarrier\Dto\Casters\ContactStringCaster;
use MailCarrier\Dto\Validators\Email;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Casters\ArrayCaster;
use Spatie\DataTransferObject\DataTransferObject;

class RecipientDto extends DataTransferObject
{
    #[Email]
    public string $email;

    /** @var array<string, mixed> */
    public array $variables = [];

    /** @var \MailCarrier\Dto\ContactDto[]|null */
    #[CastWith(ArrayCaster::class, itemType: ContactStringCaster::class)]
    public ?array $cc;

    /** @var \MailCarrier\Dto\ContactDto[]|null */
    #[CastWith(ArrayCaster::class, itemType: ContactStringCaster::class)]
    public ?array $bcc;

    /** @var \Illuminate\Http\UploadedFile[] */
    public array $attachments = [];

    /** @var \MailCarrier\Dto\RemoteAttachmentDto[] */
    #[CastWith(ArrayCaster::class, itemType: RemoteAttachmentDto::class)]
    public array $remoteAttachments = [];
}
