<?php

namespace MailCarrier\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use MailCarrier\Mcp\Tools\CreateTemplateTool;
use MailCarrier\Mcp\Tools\GetTemplateTool;
use MailCarrier\Mcp\Tools\ListTemplatesTool;
use MailCarrier\Mcp\Tools\RenderTemplateTool;
use MailCarrier\Mcp\Tools\SendTestEmailTool;
use MailCarrier\Mcp\Tools\UpdateTemplateTool;

#[Name('MailCarrier Templates')]
#[Version('1.0.0')]
#[Instructions(<<<'MARKDOWN'
    This MCP server lets AI agents inspect, create, and edit MailCarrier email templates.

    Templates use the Twig templating syntax (https://twig.symfony.com) and may optionally
    extend a shared layout. Each template is identified by a unique "slug" that is used as the
    "template" key when sending emails through the MailCarrier API.

    Use the list and get tools to inspect existing templates before editing them. Locked
    templates cannot be modified.
    MARKDOWN)]
class TemplatesServer extends Server
{
    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        ListTemplatesTool::class,
        GetTemplateTool::class,
        CreateTemplateTool::class,
        UpdateTemplateTool::class,
        RenderTemplateTool::class,
        SendTestEmailTool::class,
    ];
}
