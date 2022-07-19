<?php

namespace MailCarrier\MailCarrier\Http;

use Illuminate\Http\Response;

class GenericFile
{
    public function __construct(
        public readonly string $content,
        public readonly string $fileName
    ) {
        //
    }

    /**
     * Return the current file as array to download.
     *
     * @return array
     */
    public function forDownload(): array
    {
        return [
            $this->content,
            $this->fileName,
        ];
    }

    /**
     * Start a download for the current file.
     */
    public function asDownload(): Response
    {
        return ApiResponse::downloadContent(
            ...$this->forDownload()
        );
    }
}
