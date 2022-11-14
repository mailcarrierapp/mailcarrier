<?php

namespace MailCarrier\Enums\Dashboard;

enum SentFailureChartFilter: string
{
    case Today = 'today';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
}
