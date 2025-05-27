<?php

namespace MailCarrier\Webhooks\Strategies;

use MailCarrier\Webhooks\Dto\WebhookData;
use MailCarrier\Webhooks\Dto\IncomingWebhook;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use MailCarrier\Webhooks\Strategies\Contracts\Strategy;

class MailgunStrategy implements Strategy
{
    /**
     * Whether to log validation failures.
     */
    public function isVerbose(): bool
    {
        return Config::get('mailcarrier.webhooks.providers.mailgun.verbose', false);
    }

    /**
     * Whether to throw an exception on validation failure instead of continuing.
     */
    public function isFatal(): bool
    {
        return Config::get('mailcarrier.webhooks.providers.mailgun.fatal', false);
    }

    /**
     * Get the Mailgun webhook secret from config.
     */
    private function getSecret(): string
    {
        $secret = Config::get('mailcarrier.webhooks.providers.mailgun.secret');

        if (empty($secret)) {
            throw new \RuntimeException('Mailgun webhook secret is not configured. Please set MAILGUN_WEBHOOK_SECRET in your .env file.');
        }

        return $secret;
    }

    /**
     * Validate the webhook signature using Mailgun's algorithm.
     *
     * @see https://documentation.mailgun.com/docs/mailgun/user-manual/tracking-messages/#securing-webhooks
     */
    public function validate(IncomingWebhook $webhook): bool
    {
        $signature = $webhook->getBodyValue('signature');

        if (!isset($signature['timestamp'], $signature['token'], $signature['signature'])) {
            return false;
        }

        // Concatenate timestamp and token
        $data = $signature['timestamp'] . $signature['token'];

        // Calculate HMAC using SHA256 with secret from config
        $calculatedSignature = hash_hmac('sha256', $data, $this->getSecret());

        // Compare signatures
        return hash_equals($calculatedSignature, $signature['signature']);
    }

    /**
     * Extract structured data from Mailgun's webhook payload.
     *
     * @param array $payload The raw webhook payload from Mailgun
     * @return WebhookData
     */
    public function extract(array $payload): WebhookData
    {
        if (!isset($payload['event-data'])) {
            throw new \InvalidArgumentException('Invalid Mailgun webhook payload: missing event-data');
        }

        $eventData = $payload['event-data'];

        if (!isset($eventData['id'], $eventData['event'], $eventData['timestamp'])) {
            throw new \InvalidArgumentException('Invalid Mailgun webhook payload: missing required fields');
        }

        return new WebhookData(
            messageId: $eventData['id'],
            eventName: $eventData['event'],
            date: CarbonImmutable::createFromTimestamp($eventData['timestamp'])
        );
    }
}
