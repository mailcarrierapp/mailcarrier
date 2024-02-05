<?php

namespace MailCarrier;

use Closure;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\AbstractUser;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MailCarrierManager
{
    protected ?Closure $sendingMiddleware = null;

    protected ?Closure $beforeSendingMiddleware = null;

    protected ?Closure $socialAuthGate = null;

    /**
     * Intercept a mail ready to be sent and write a middleware around it.
     *
     * @param  Closure(\MailCarrier\Dto\GenericMailDto $mail, \Closure $next): void  $callback
     */
    public function sending(Closure $callback): void
    {
        $this->sendingMiddleware = $callback;
    }

    /**
     * Invoke a custom callback before a sending a mail (sync).
     *
     * @param  Closure(\MailCarrier\Dto\GenericMailDto $mail): void  $callback
     */
    public function beforeSending(Closure $callback): void
    {
        $this->beforeSendingMiddleware = $callback;
    }

    /**
     * Define the gate to authorize a user via social authentication.
     *
     * @param  Closure(\Laravel\Socialite\AbstractUser $user): bool  $callback
     */
    public function authorizeSocialAuth(Closure $callback): void
    {
        $this->socialAuthGate = $callback;
    }

    /**
     * Check if a user is allowed to authenticate via social.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function validateSocialAuth(AbstractUser $user): void
    {
        $socialAuthGate = $this->socialAuthGate;

        if (is_null($socialAuthGate) || !$socialAuthGate($user)) {
            throw new UnauthorizedHttpException('OAuth');
        }
    }

    /**
     * Get the sending middleware callback.
     */
    public function getSendingMiddleware(): ?Closure
    {
        return $this->sendingMiddleware;
    }

    /**
     * Get the sending middleware callback.
     */
    public function getBeforeSendingMiddleware(): ?Closure
    {
        return $this->beforeSendingMiddleware;
    }

    /**
     * Get the Social Auth driver.
     */
    public function getSocialAuthDriver(): ?string
    {
        return Config::get('mailcarrier.social_auth_driver');
    }

    /**
     * Get the disk storage.
     */
    public function storage(?string $disk = null): Filesystem
    {
        return Storage::disk($disk ?: Config::get('mailcarrier.attachments.disk'));
    }

    /**
     * Determine if the a file exists on the disk.
     */
    public function fileExists(string $resource, ?string $disk = null): bool
    {
        return $this->storage($disk)->exists($resource);
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
