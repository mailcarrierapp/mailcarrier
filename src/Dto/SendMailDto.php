<?php

namespace MailCarrier\Dto;

use MailCarrier\Dto\Casters\ContactStringCaster;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Casters\ArrayCaster;
use Spatie\DataTransferObject\DataTransferObject;

class SendMailDto extends DataTransferObject
{
    public bool $enqueue = false;

    public string $template;

    public string $subject;

    #[CastWith(ContactStringCaster::class)]
    public ?ContactDto $sender;

    public ?string $recipient;

    #[CastWith(ContactStringCaster::class)]
    public ?ContactDto $cc;

    #[CastWith(ContactStringCaster::class)]
    public ?ContactDto $bcc;

    /** @var array<string, mixed> */
    public array $variables = [];

    /** @var \MailCarrier\MailCarrier\Dto\RecipientDto[]|null */
    #[CastWith(ArrayCaster::class, itemType: RecipientDto::class)]
    public ?array $recipients;

    public ?string $trigger;

    /** @var \Illuminate\Http\UploadedFile[] */
    public array $attachments = [];

    /** @var \MailCarrier\MailCarrier\Dto\RemoteAttachmentDto[] */
    #[CastWith(ArrayCaster::class, itemType: RemoteAttachmentDto::class)]
    public array $remoteAttachments = [];
}
