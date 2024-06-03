<?php

namespace MailCarrier\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use MailCarrier\Actions\Logs\ResendEmail;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Models\Log;

class LogRetryCommand extends Command
{
    public $signature = 'mailcarrier:log-retry {--date=}';

    public $description = 'Retry sending emails from log that failed.';

    public function handle(): int
    {
        $this->info('Retrying failed logs...');

        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::now()->subDay();

        $resendEmailAction = new ResendEmail();

        $retriedCount = tap(
            Log::query()
                ->whereDate('created_at', '=', $date)
                ->where('status', LogStatus::Failed)
                ->get()
        )
            ->each(fn (Log $log) => $resendEmailAction->run($log))
            ->count();

        $this->info($retriedCount . ' ' . Str::plural('log', $retriedCount) . ' retried.');

        return self::SUCCESS;
    }
}
