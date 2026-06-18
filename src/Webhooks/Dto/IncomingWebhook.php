<?php

namespace MailCarrier\Webhooks\Dto;

use Illuminate\Support\Arr;

class IncomingWebhook
{
    /**
     * @param  array<string, string|null>  $headers  First value per header name.
     * @param  array<string, mixed>  $body
     */
    public function __construct(
        public readonly array $headers,
        public readonly array $body,
    ) {}

    /**
     * Get a header value by name.
     */
    public function getHeader(string $name, ?string $default = null): ?string
    {
        return Arr::get($this->headers, $name, $default);
    }

    /**
     * Get a body value by key using dot notation.
     */
    public function getBodyValue(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->body, $key, $default);
    }
}
