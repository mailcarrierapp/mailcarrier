<?php

namespace MailCarrier\Dto;

use MailCarrier\Dto\Attributes\CastWith;
use MailCarrier\Dto\Casters\ArrayCaster;
use MailCarrier\Dto\Casters\ContactArrayCaster;
use MailCarrier\Dto\Validators\Email;

class RecipientDto extends DataTransferObject
{
    #[Email]
    public string $email;

    /** @var array<string, mixed> */
    public array $variables = [];

    /** @var \MailCarrier\Dto\ContactDto[]|null */
    #[CastWith(ContactArrayCaster::class)]
    public ?array $cc;

    /** @var \MailCarrier\Dto\ContactDto[]|null */
    #[CastWith(ContactArrayCaster::class)]
    public ?array $bcc;

    /** @var \MailCarrier\Dto\AttachmentDto[] */
    public array $attachments = [];

    /** @var \MailCarrier\Dto\RemoteAttachmentDto[] */
    #[CastWith(ArrayCaster::class, itemType: RemoteAttachmentDto::class)]
    public array $remoteAttachments = [];
}
