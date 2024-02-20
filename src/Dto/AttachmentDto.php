<?php

namespace MailCarrier\Dto;

use Illuminate\Http\UploadedFile;

class AttachmentDto
{
    public static function fromUploadedFile(UploadedFile $file): static
    {
        return new static(
            name: $file->getClientOriginalName(),
            content: base64_encode($file->getContent()),
            size: $file->getSize(),
        );
    }

    /**
     * @param string $content Base64 encoded file content
     */
    public function __construct(
        public readonly string $name,
        public readonly string $content,
        public readonly int $size
    ) {
        //
    }
}
