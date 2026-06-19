<?php

use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Facades\Mcp;
use MailCarrier\Mcp\Servers\TemplatesServer;

if (Config::boolean('mailcarrier.mcp.enabled')) {
    Mcp::oauthRoutes();

    Mcp::web(Config::string('mailcarrier.mcp.path'), TemplatesServer::class)
        ->middleware(Config::array('mailcarrier.mcp.middleware'));
}
