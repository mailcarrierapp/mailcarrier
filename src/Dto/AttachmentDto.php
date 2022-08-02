<?php

namespace MailCarrier\Dto;

use Illuminate\Http\UploadedFile;
use Spatie\DataTransferObject\DataTransferObject;

class AttachmentDto extends DataTransferObject
{
    public readonly string $name;

    public readonly string $content;

    public readonly int $size;

    public function __construct(UploadedFile $file)
    {
        $this->name = $file->getClientOriginalName();
        $this->content = base64_encode($file->getContent());
        $this->size = $file->getSize();
    }
}
