<?php

namespace MailCarrier\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Database\Eloquent\Builder;
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

        $failed = Log::query()
            ->when(
                $this->option('date'),
                fn (Builder $query) => $query->whereDate('created_at', $this->option('date'))
            )
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
