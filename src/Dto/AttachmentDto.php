<?php

namespace MailCarrier\Dto;

use Illuminate\Http\UploadedFile;
use Spatie\DataTransferObject\DataTransferObject;

class AttachmentDto extends DataTransferObject
{
    public string $name;

    public string $content;

    public int $size;

    public function __construct(UploadedFile $file)
    {
        $this->name = $file->getClientOriginalName();
        $this->content = base64_encode($file->getContent());
        $this->size = $file->getSize();
    }
}
