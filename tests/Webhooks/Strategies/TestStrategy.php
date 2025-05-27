<?php

namespace MailCarrier\Tests\Webhooks\Strategies;

use Carbon\CarbonImmutable;
use MailCarrier\Webhooks\Dto\IncomingWebhook;
use MailCarrier\Webhooks\Dto\WebhookData;
use MailCarrier\Webhooks\Strategies\Contracts\Strategy;

class TestStrategy implements Strategy
{
    public function __construct(
        private readonly bool $shouldValidate = true,
        private readonly bool $isVerbose = false,
        private readonly bool $isFatal = false,
    ) {
    }

    public function isVerbose(): bool
    {
        return $this->isVerbose;
    }

    public function isFatal(): bool
    {
        return $this->isFatal;
    }

    public function validate(IncomingWebhook $webhook): bool
    {
        return $this->shouldValidate;
    }

    public function extract(array $payload): WebhookData
    {
        return new WebhookData(
            messageId: 'test-message-id',
            eventName: 'test-event',
            date: CarbonImmutable::now(),
        );
    }
}
