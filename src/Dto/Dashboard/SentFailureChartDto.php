<?php

namespace MailCarrier\Dto\Dashboard;

use Illuminate\Support\Collection;
use MailCarrier\Dto\DataTransferObject;

class SentFailureChartDto extends DataTransferObject
{
    /** @var \Illuminate\Support\Collection<int, \Flowframe\Trend\TrendValue> */
    public Collection $sent;

    /** @var \Illuminate\Support\Collection<int, \Flowframe\Trend\TrendValue> */
    public Collection $failure;
}
