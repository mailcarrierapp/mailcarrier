<?php

namespace MailCarrier\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use MailCarrier\Actions\Templates\Preview;

class TemplateController extends Controller
{
    /**
     * Preview a template.
     */
    public function preview(Request $request, Preview $preview): Response
    {
        $token = $request->query('token') ?: throw new \Exception('No preview token provided.');

        return new Response(
            $preview->run(Cache::get('preview:' . $token))
        );
    }
}
