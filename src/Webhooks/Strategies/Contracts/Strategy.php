<?php

namespace MailCarrier\Webhooks\Strategies\Contracts;

use MailCarrier\Webhooks\Dto\IncomingWebhook;
use MailCarrier\Webhooks\Dto\WebhookData;

interface Strategy
{
    /**
     * Whether to log validation failures.
     */
    public function isVerbose(): bool;

    /**
     * Whether to throw an exception on validation failure instead of continuing.
     */
    public function isFatal(): bool;

    /**
     * Validate the incoming webhook.
     */
    public function validate(IncomingWebhook $webhook): bool;

    /**
     * Extract structured data from the webhook payload.
     */
    public function extract(array $payload): WebhookData;
}
