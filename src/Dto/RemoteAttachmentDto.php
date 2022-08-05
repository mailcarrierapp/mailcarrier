<?php

namespace MailCarrier\Dto;

use Spatie\DataTransferObject\DataTransferObject;

class RemoteAttachmentDto extends DataTransferObject
{
    public string $resource;

    public ?string $name = null;

    public ?string $disk = null;
}
