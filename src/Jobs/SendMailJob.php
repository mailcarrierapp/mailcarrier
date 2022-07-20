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
use MailCarrier\Mail\GenericMail;
use MailCarrier\Models\Log;

class SendMailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
    }
}
