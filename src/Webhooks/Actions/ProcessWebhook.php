<?php

namespace MailCarrier\Webhooks\Actions;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MailCarrier\Actions\Action;
use MailCarrier\Models\Log as LogModel;
use MailCarrier\Webhooks\Dto\IncomingWebhook;
use MailCarrier\Webhooks\Dto\WebhookData;
use MailCarrier\Webhooks\Exceptions\WebhookValidationException;

class ProcessWebhook extends Action
{
    /**
     * Process the incoming webhook using all configured strategies.
     *
     * @throws \MailCarrier\Webhooks\Exceptions\WebhookValidationException
     */
    public function run(IncomingWebhook $webhook): void
    {
        /** @var array<class-string<\MailCarrier\Webhooks\Strategies\Contracts\Strategy>> $strategies */
        $strategies = Config::get('mailcarrier.webhooks.strategies', []);

        foreach ($strategies as $strategyClass) {
            /** @var \MailCarrier\Webhooks\Strategies\Contracts\Strategy $strategy */
            $strategy = App::make($strategyClass);

            if (!$strategy->validate($webhook)) {
                if ($strategy->isVerbose()) {
                    Log::warning('Webhook validation failed', [
                        'strategy' => Str::of($strategyClass)
                            ->afterLast('\\')
                            ->remove('::class')
                            ->toString(),
                    ]);
                }

                if ($strategy->isFatal()) {
                    throw new WebhookValidationException;
                }

                continue;
            }

            $data = $strategy->extract($webhook->body);

            $this->updateLog($data);

            // If we found a valid strategy and updated the log, we can stop
            break;
        }
    }

    /**
     * Create a new log event with webhook data.
     */
    private function updateLog(WebhookData $data): void
    {
        LogModel::query()
            ->firstWhere('message_id', $data->messageId)
            ?->events()
            ?->create([
                'name' => $data->eventName,
                'createdAt' => $data->date,
            ]);
    }
}
