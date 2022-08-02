<?php

namespace MailCarrier\Dto;

use Spatie\DataTransferObject\DataTransferObject;

class RemoteAttachmentDto extends DataTransferObject
{
    public string $resource;

    public ?string $disk = null;
}
