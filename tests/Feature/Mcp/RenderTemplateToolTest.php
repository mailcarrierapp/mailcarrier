<?php

use MailCarrier\Mcp\Servers\TemplatesServer;
use MailCarrier\Mcp\Tools\RenderTemplateTool;
use MailCarrier\Models\Template;

it('renders a template with the given variables', function () {
    Template::factory()->create([
        'slug' => 'greeting',
        'layout_id' => null,
        'content' => '<p>Hello {{ name }}</p>',
    ]);

    TemplatesServer::tool(RenderTemplateTool::class, [
        'slug' => 'greeting',
        'variables' => ['name' => 'Danilo'],
    ])
        ->assertOk()
        ->assertSee('Hello Danilo');
});

it('errors when the template is not found', function () {
    TemplatesServer::tool(RenderTemplateTool::class, ['slug' => 'missing'])
        ->assertHasErrors(['No template found']);
});
