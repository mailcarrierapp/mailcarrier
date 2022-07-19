<?php

namespace MailCarrier\MailCarrier\Actions;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use MailCarrier\MailCarrier\Dto\AttachmentDto;
use MailCarrier\MailCarrier\Dto\ContactDto;
use MailCarrier\MailCarrier\Dto\GenericMailDto;
use MailCarrier\MailCarrier\Dto\RecipientDto;
use MailCarrier\MailCarrier\Dto\SendMailDto;
use MailCarrier\MailCarrier\Exceptions\MissingVariableException;
use MailCarrier\MailCarrier\Jobs\SendMailJob;
use MailCarrier\MailCarrier\Models\Template;

class SendMail extends Action
{
    protected SendMailDto $params;

    protected Template $template;

    /** @var \MailCarrier\MailCarrier\Dto\RecipientDto[] */
    protected array $recipients;

    /** @var array<string, string> */
    protected array $templateRenderCache;

    protected bool $shouldLog = true;

    /**
     * Send or enqueue the email.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function run(SendMailDto $params): void
    {
        $this->params = $params;

        // Fallback sender to app defaults
        if (!$params->sender) {
            $this->params->sender = new ContactDto(
                name: Config::get('mail.from.name'),
                email: Config::get('mail.from.address')
            );
        }

        $this->template = (new Templates\FindBySlug)->run($params->template);
        $this->recipients = $params->recipients ?: [
            new RecipientDto(
                recipient: $params->recipient,
            ),
        ];

        // Rebuild recipients merging with default/global values
        $this->recipients = array_map(
            fn (RecipientDto $recipient) => new RecipientDto([
                ...$recipient->toArray(),
                'cc' => $recipient->cc ?: $params->cc,
                'bcc' => $recipient->bcc ?: $params->bcc,
                'variables' => [
                    ...$params->variables ?: [],
                    ...$recipient->variables ?: [],
                ],
                'attachments' => [
                    ...$params->attachments ?: [],
                    ...$recipient->attachments ?: [],
                ],
                'remoteAttachments' => [
                    ...$params->remoteAttachments ?: [],
                    ...$recipient->remoteAttachments ?: [],
                ],
            ]),
            $this->recipients
        );

        foreach ($this->recipients as $recipient) {
            $this->send($recipient);
        }
    }

    /**
     * Disable logs of sent emails.
     */
    public function withoutLogging(): static
    {
        $this->shouldLog = false;

        return $this;
    }

    /**
     * Send the email to the given recipient.
     */
    protected function send(RecipientDto $recipient): void
    {
        $queueEnabled = Config::get('mailcarrier.queue.enabled', false);
        $queueForced = Config::get('mailcarrier.queue.force', false);
        $shouldBeQueued = ($this->params->enqueue && $queueEnabled) || $queueForced;

        $templateRender = null;
        $exception = null;

        try {
            $templateRender = (new Templates\Render)->run($this->template, $recipient->variables);
        } catch (Exception $e) {
            $exception = new Exception($e->getMessage());

            if (str_contains($e->getMessage(), 'Undefined variable')) {
                $missingVariableName = Str::match('/Undefined variable \$([\w\d_]+)/i', $e->getMessage());
                $exception = new MissingVariableException(
                    sprintf(
                        'Missing variable "%s" for template "%s"',
                        $missingVariableName,
                        $this->template->name
                    )
                );
            }
        }

        $genericMailDto = new GenericMailDto(
            trigger: $this->params->trigger,
            sender: $this->params->sender,
            recipient: $recipient->recipient,
            cc: $recipient->cc,
            bcc: $recipient->bcc,
            subject: $this->params->subject,
            attachments: array_map(
                fn (UploadedFile $file) => new AttachmentDto($file),
                $recipient->attachments
            ),
            remoteAttachments: $recipient->remoteAttachments,
            template: $this->template,
            variables: $recipient->variables,
            content: $templateRender,
            error: $exception->getMessage(),
        );

        $log = !$this->shouldLog ? null : (new Logs\CreateFromGenericMail)->run($genericMailDto);

        if ($exception) {
            throw $exception;
        }

        if ($shouldBeQueued) {
            SendMailJob::dispatch($genericMailDto, $log)
                ->onQueue(Config::get('mailcarrier.queue.name'))
                ->onConnection(Config::get('mailcarrier.queue.connection'));
        } else {
            SendMailJob::dispatchSync($genericMailDto, $log);
        }
    }
}
