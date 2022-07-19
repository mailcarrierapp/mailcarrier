<?php

namespace MailCarrier\MailCarrier\Actions\Logs;

use MailCarrier\MailCarrier\Actions\Action;
use MailCarrier\MailCarrier\Models\Log;

class Update extends Action
{
    /**
     * Insert multiple logs at once.
     */
    public function run(Log $log, array $data): Log
    {
        return tap($log)->update($data);
    }
}
