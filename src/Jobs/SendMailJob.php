<?php

namespace MailCarrier\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use MailCarrier\Actions\Logs;
use MailCarrier\Dto\GenericMailDto;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Exceptions\SendingFailedException;
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
    public int $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(protected GenericMailDto $genericMailDto, protected ?Log $log = null)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $error = null;

        try {
            Mail::send(new GenericMail($this->genericMailDto));
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        if ($this->log) {
            (new Logs\Update)->run($this->log, [
                'status' => $error ? LogStatus::Failed : LogStatus::Sent,
                'error' => $error,
            ]);
        }

        if ($error) {
            throw (new SendingFailedException($error))->setLog($this->log);
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [
            5, // 5sec
            30, // 30sec
            60, // 1min
            60 * 5, // 5min
            60 * 30, // 30min
        ];
    }
}
