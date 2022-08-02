<?php

namespace MailCarrier\Http\Controllers;

use Illuminate\Http\Response;
use MailCarrier\Models\Log;

class LogController extends Controller
{
    /**
     * Preview a log.
     */
    public function preview(Log $log): Response
    {
        return new Response(
            $log->template_frozen->render
        );
    }
}
