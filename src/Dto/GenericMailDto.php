<?php

namespace MailCarrier\Dto;

use MailCarrier\Models\Template;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Casters\ArrayCaster;
use Spatie\DataTransferObject\DataTransferObject;

class GenericMailDto extends DataTransferObject
{
    public ?string $trigger;

    public ?string $content;

    public ?string $subject;

    public ?string $error;

    public string $recipient;

    public ?ContactDto $sender;

    public ?ContactDto $cc;

    public ?ContactDto $bcc;

    public Template $template;

    public array $variables = [];

    /** @var \MailCarrier\Dto\AttachmentDto[] */
    public array $attachments = [];

    /** @var \MailCarrier\Dto\RemoteAttachmentDto[] */
    #[CastWith(ArrayCaster::class, itemType: RemoteAttachmentDto::class)]
    public array $remoteAttachments = [];

    public array $tags = [];

    /** @var array<string, mixed> */
    public array $metadata = [];
}
