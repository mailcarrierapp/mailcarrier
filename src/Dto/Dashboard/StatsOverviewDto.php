<?php

namespace MailCarrier\Dto\Dashboard;

use Spatie\DataTransferObject\DataTransferObject;

class StatsOverviewDto extends DataTransferObject
{
    public int $sent;

    public int $pending;

    public int $failed;

    public int $failurePercentage;

    public array $sentLastWeek;

    public array $failedLastWeek;
}
