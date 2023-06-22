<?php

namespace MailCarrier\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use MailCarrier\Dto\GenericMailDto;

class GenericMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(protected GenericMailDto $params)
    {
        //
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        foreach ($this->params->attachments as $attachment) {
            $this->attachData(
                base64_decode($attachment->content),
                $attachment->name,
            );
        }

        foreach ($this->params->remoteAttachments as $attachment) {
            $this->attachFromStorageDisk(
                $attachment->disk ?: Config::get('mailcarrier.attachments.disk'),
                $attachment->resource,
                $attachment->name,
            );
        }

        foreach ($this->params->tags as $tag) {
            $this->tag($tag);
        }

        foreach ($this->params->metadata as $metaName => $metaValue) {
            $this->metadata($metaName, $metaValue);
        }

        return $this
            ->to($this->params->recipient)
            ->html($this->params->content)
            ->when(
                $this->params->sender,
                callback: fn (GenericMail $mail) => $mail->from($this->params->sender->email, $this->params->sender->name),
                default: fn (GenericMail $mail) => $mail->from(Config::get('mail.from.address'), Config::get('mail.from.name'))
            )
            ->when(
                $this->params->subject,
                fn (GenericMail $mail) => $mail->subject($this->params->subject)
            )
            ->when(
                $this->params->cc,
                fn (GenericMail $mail) => $mail->cc($this->params->cc->email, $this->params->cc->name)
            )
            ->when(
                $this->params->bcc,
                fn (GenericMail $mail) => $mail->bcc($this->params->bcc->email, $this->params->bcc->name)
            );
    }

    /**
     * Test the mail content against the given value.
     */
    public function hasHtml(string $html): bool
    {
        return $this->html === $html;
    }
}
