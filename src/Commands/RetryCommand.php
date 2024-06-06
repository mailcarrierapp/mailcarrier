<?php

namespace MailCarrier\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use MailCarrier\Actions\Logs\ResendEmail;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Models\Log;

class RetryCommand extends Command
{
    public $signature = 'mailcarrier:retry {--date=}';

    public $description = 'Retry failed emails.';

    public function handle(ResendEmail $resend): int
    {
        $this->info('Retrying failed emails...');

        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::now()->subDay();

        $failed = Log::query()
            ->whereDate('created_at', '=', $date)
            ->where('status', LogStatus::Failed)
            ->get();

        $this->withProgressBar(
            $failed,
            fn (Log $log) => $resend->run($log)
        );

        $this->info('Failed emails retried.');

        return self::SUCCESS;
    }
}
