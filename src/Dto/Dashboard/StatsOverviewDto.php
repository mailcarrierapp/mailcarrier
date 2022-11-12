<?php

namespace MailCarrier\Dto\Dashboard;

use Spatie\DataTransferObject\DataTransferObject;

class StatsOverviewDto extends DataTransferObject
{
    public readonly int $sent;

    public readonly int $pending;

    public readonly int $failed;

    public readonly int $failurePercentage;

    public array $sentLastWeek;

    public array $failedLastWeek;
}
