<?php

namespace MailCarrier\Tests\Feature\Webhooks\Strategies;

use Carbon\CarbonImmutable;
use MailCarrier\Webhooks\Dto\IncomingWebhook;
use MailCarrier\Webhooks\Dto\WebhookData;
use MailCarrier\Webhooks\Strategies\Contracts\Strategy;

class FatalFailingStrategy implements Strategy
{
    public function validate(IncomingWebhook $webhook): bool
    {
        return false;
    }

    public function extract(array $body): WebhookData
    {
        return new WebhookData(
            messageId: 'test-message-id',
            eventName: 'test-event',
            date: CarbonImmutable::now(),
        );
    }

    public function isVerbose(): bool
    {
        return false;
    }

    public function isFatal(): bool
    {
        return true;
    }
}
