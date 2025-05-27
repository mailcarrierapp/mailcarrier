<?php

namespace MailCarrier\Webhooks\Dto;

use Carbon\CarbonImmutable;

class WebhookData
{
    public function __construct(
        public readonly string $messageId,
        public readonly string $eventName,
        public readonly CarbonImmutable $date,
    ) {}
}
