<?php

namespace MailCarrier\Dto\Dashboard;

use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;

class SentFailureChartDto extends DataTransferObject
{
    /** @var \Illuminate\Support\Collection<int, Flowframe\Trend\TrendValue> */
    public Collection $sent;

    /** @var \Illuminate\Support\Collection<int, Flowframe\Trend\TrendValue> */
    public Collection $failure;
}
