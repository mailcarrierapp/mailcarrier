<?php

namespace MailCarrier\MailCarrier;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class MailCarrierManager
{
    /**
     * Get the disk storage.
     */
    public function storage(?string $disk = null): Filesystem
    {
        return Storage::disk($disk ?: Config::get('mailcarrier.attachments.disk'));
    }

    /**
     * Upload a file to the storage disk.
     *
     * @return string The file path
     */
    public function upload(string $content, string $fileName): string
    {
        $filePath = $this->hashFileName($fileName);

        $uploadResponse = $this->storage()->put(
            $filePath,
            base64_decode($content)
        );

        if (!$uploadResponse) {
            throw new UploadException();
        }

        return $filePath;
    }

    /**
     * Download a file from the storage disk.
     */
    public function download(string $resource, ?string $disk = null): ?string
    {
        $content = $this->storage($disk)->get($resource);

        return $content ? base64_encode($content) : null;
    }

    /**
     * Get the file size from the storage disk.
     */
    public function getFileSize(string $resource, ?string $disk = null): int
    {
        return $this->storage($disk)->size($resource);
    }

    /**
     * Upload a file to the storage disk.
     */
    public function hashFileName(string $fileName): string
    {
        $extension = Str::afterLast($fileName, '.');

        return Config::get('mailcarrier.attachments.path', '') .
            md5($fileName . Str::random() . Str::uuid()->toString()) .
            '.' .
            $extension;
    }

    /**
     * Convert bytes into human readable value.
     */
    public function humanBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
