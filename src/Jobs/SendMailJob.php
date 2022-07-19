<?php

namespace MailCarrier\MailCarrier\Jobs;

use MailCarrier\MailCarrier\Actions\Logs;
use MailCarrier\MailCarrier\Dto\GenericMailDto;
use MailCarrier\MailCarrier\Enums\LogStatus;
use MailCarrier\MailCarrier\Mail\GenericMail;
use MailCarrier\MailCarrier\Models\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
