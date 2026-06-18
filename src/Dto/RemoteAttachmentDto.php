<?php

namespace MailCarrier\Dto;

class RemoteAttachmentDto extends DataTransferObject
{
    public string $resource;

    public ?string $name = null;

    public ?string $disk = null;
}
