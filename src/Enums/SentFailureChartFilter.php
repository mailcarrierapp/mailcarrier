<?php

namespace MailCarrier\Enums;

enum SentFailureChartFilter: string
{
    case Today = 'today';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
}
