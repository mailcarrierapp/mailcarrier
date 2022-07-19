<?php

namespace MailCarrier\MailCarrier;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class MailCarrier
{
    /**
     * Get the disk storage.
     */
    public static function storage(?string $disk = null): Filesystem
    {
        return Storage::disk($disk ?: Config::get('mailcarrier.attachments.disk'));
    }

    /**
     * Upload a file to the storage disk.
     *
     * @return string The file path
     */
    public static function upload(string $content, string $fileName): string
    {
        $filePath = static::hashFileName($fileName);

        $uploadResponse = static::storage()->put(
            $filePath,
            base64_decode($content)
        );

        if (! $uploadResponse) {
            throw new UploadException();
        }

        return $filePath;
    }

    /**
     * Download a file from the storage disk.
     */
    public static function download(string $resource, ?string $disk = null): ?string
    {
        $content = static::storage($disk)->get($resource);

        return $content ? base64_encode($content) : null;
    }

    /**
     * Get the file size from the storage disk.
     */
    public static function getFileSize(string $resource, ?string $disk = null): int
    {
        return static::storage($disk)->size($resource);
    }

    /**
     * Upload a file to the storage disk.
     */
    public static function hashFileName(string $fileName): string
    {
        $extension = Str::afterLast($fileName, '.');

        return Config::get('mailcarrier.attachments.path', '').
            md5($fileName.Str::random().Str::uuid()->toString()).
            '.'.
            $extension;
    }

    /**
     * Convert bytes into human readable value.
     */
    public static function humanBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
