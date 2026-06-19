<?php

use MailCarrier\Mcp\Servers\TemplatesServer;
use MailCarrier\Mcp\Tools\UpdateTemplateTool;
use MailCarrier\Models\Template;
use function Pest\Laravel\assertDatabaseHas;

it('updates a template content by slug', function () {
    Template::factory()->create([
        'slug' => 'welcome',
        'content' => '<p>Old</p>',
    ]);

    TemplatesServer::tool(UpdateTemplateTool::class, [
        'slug' => 'welcome',
        'content' => '<p>New</p>',
    ])->assertOk();

    assertDatabaseHas(Template::class, [
        'slug' => 'welcome',
        'content' => '<p>New</p>',
    ]);
});

it('updates a template by id', function () {
    $template = Template::factory()->create(['name' => 'Old name']);

    TemplatesServer::tool(UpdateTemplateTool::class, [
        'id' => $template->id,
        'name' => 'New name',
    ])->assertOk();

    assertDatabaseHas(Template::class, [
        'id' => $template->id,
        'name' => 'New name',
    ]);
});

it('refuses to edit a locked template', function () {
    Template::factory()->create([
        'slug' => 'locked',
        'is_locked' => true,
        'content' => '<p>Locked</p>',
    ]);

    TemplatesServer::tool(UpdateTemplateTool::class, [
        'slug' => 'locked',
        'content' => '<p>Hacked</p>',
    ])->assertHasErrors(['locked']);

    assertDatabaseHas(Template::class, [
        'slug' => 'locked',
        'content' => '<p>Locked</p>',
    ]);
});

it('errors when the template is not found', function () {
    TemplatesServer::tool(UpdateTemplateTool::class, [
        'slug' => 'missing',
        'content' => '<p>Nope</p>',
    ])->assertHasErrors(['No template found']);
});
