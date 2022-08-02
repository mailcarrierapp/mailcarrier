<?php

namespace MailCarrier\Enums;

use MailCarrier\Concerns\EnumSerializable;

enum LogStatus: string
{
    use EnumSerializable;

    case Pending = 'PENDING';
    case Failed = 'FAILED';
    case Sent = 'SENT';
}
