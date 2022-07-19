<?php

namespace MailCarrier\MailCarrier\Http\Controllers;

use Illuminate\Http\Response;
use MailCarrier\MailCarrier\Models\Log;

class PreviewController extends Controller
{
    /**
     * Preview a log.
     */
    public function log(Log $log): Response
    {
        return new Response(
            $log->template_frozen->render
        );
    }
}
