<?php

namespace MailCarrier\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\SentMessage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use MailCarrier\Actions\Logs;
use MailCarrier\Dto\GenericMailDto;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Exceptions\SendingFailedException;
use MailCarrier\Facades\MailCarrier;
use MailCarrier\Mail\GenericMail;
use MailCarrier\Models\Log;

class SendMailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 6;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected GenericMailDto $genericMailDto,
        protected ?Log $log = null,
        protected ?string $sendingMiddleware = null
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Prevent sending already sent logs, e.g. from manual retry
        if ($this->log?->status === LogStatus::Sent) {
            return;
        }

        $error = null;

        try {
            $this->send();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        if ($this->log) {
            (new Logs\Update)->run($this->log, [
                'status' => $error ? LogStatus::Failed : LogStatus::Sent,
                'error' => $error,
                'tries' => $this->log->tries + 1,
                'last_try_at' => Carbon::now(),
            ]);
        }

        if ($error) {
            throw (new SendingFailedException($error))->setLog($this->log->refresh());
        }
    }

    /**
     * Send the email by processing it into the optional user-defined middleware.
     */
    protected function send(): void
    {
        $sendMail = function (?GenericMailDto $override = null) {
            $sent = Mail::send(new GenericMail($override ?: $this->genericMailDto));

            if ($sent instanceof SentMessage) {
                // MailGun for example sends <foo@domain.org> as value, but then in the Webhooks there are no < >
                $this->log->message_id = str_replace(['<', '>'], '', $sent->getMessageId());
                $this->log->save();
            }
        };

        if ($this->sendingMiddleware) {
            $middleware = unserialize($this->sendingMiddleware)->getClosure();
            $middleware($this->genericMailDto, $sendMail);
        } else {
            $sendMail();
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return MailCarrier::getEmailRetriesBackoff();
    }
}
