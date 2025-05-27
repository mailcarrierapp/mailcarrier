<?php

namespace MailCarrier\Webhooks\Dto;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class IncomingWebhook
{
    /**
     * @param Collection<string, string> $headers
     * @param array<string, mixed> $body
     */
    public function __construct(
        public readonly Collection $headers,
        public readonly array $body,
    ) {}

    /**
     * Get a header value by name.
     */
    public function getHeader(string $name, ?string $default = null): ?string
    {
        return $this->headers->get($name, $default);
    }

    /**
     * Get a body value by key using dot notation.
     */
    public function getBodyValue(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->body, $key, $default);
    }
}
