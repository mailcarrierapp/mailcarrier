<?php

use MailCarrier\Mcp\Servers\TemplatesServer;
use MailCarrier\Mcp\Tools\GetTemplateTool;
use MailCarrier\Models\Template;

it('returns a template by slug', function () {
    Template::factory()->create([
        'slug' => 'welcome',
        'content' => '<p>Hello there</p>',
    ]);

    TemplatesServer::tool(GetTemplateTool::class, ['slug' => 'welcome'])
        ->assertOk()
        ->assertSee('welcome')
        ->assertSee('Hello there');
});

it('returns a template by id', function () {
    $template = Template::factory()->create(['content' => '<p>By id</p>']);

    TemplatesServer::tool(GetTemplateTool::class, ['id' => $template->id])
        ->assertOk()
        ->assertSee('By id');
});

it('errors when the template is not found', function () {
    TemplatesServer::tool(GetTemplateTool::class, ['slug' => 'missing'])
        ->assertHasErrors(['No template found']);
});

it('errors when no identifier is provided', function () {
    TemplatesServer::tool(GetTemplateTool::class)
        ->assertHasErrors();
});
