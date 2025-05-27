<?php

namespace MailCarrier\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use MailCarrier\Webhooks\Actions\ProcessWebhook;
use MailCarrier\Webhooks\Dto\IncomingWebhook;

class WebhookController extends Controller
{
    /**
     * Handle incoming webhooks from email providers.
     */
    public function __invoke(Request $request, ProcessWebhook $processWebhook): Response
    {
        $webhook = new IncomingWebhook(
            headers: new Collection($request->headers->all()),
            body: $request->all(),
        );

        $processWebhook->run($webhook);

        return new Response();
    }
}
