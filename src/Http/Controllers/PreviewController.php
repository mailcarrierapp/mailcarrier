<?php

namespace MailCarrier\MailCarrier\Http\Controllers;

use MailCarrier\MailCarrier\Models\Log;
use Illuminate\Http\Response;

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
