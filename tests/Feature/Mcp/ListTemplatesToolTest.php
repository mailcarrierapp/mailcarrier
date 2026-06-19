<?php

use MailCarrier\Mcp\Servers\TemplatesServer;
use MailCarrier\Mcp\Tools\ListTemplatesTool;
use MailCarrier\Models\Template;

it('lists all templates', function () {
    Template::factory()->create(['name' => 'Welcome', 'slug' => 'welcome']);
    Template::factory()->create(['name' => 'Invoice', 'slug' => 'invoice']);

    TemplatesServer::tool(ListTemplatesTool::class)
        ->assertOk()
        ->assertSee('welcome')
        ->assertSee('invoice');
});

it('filters templates by search term', function () {
    Template::factory()->create(['name' => 'Welcome', 'slug' => 'welcome']);
    Template::factory()->create(['name' => 'Invoice', 'slug' => 'invoice']);

    TemplatesServer::tool(ListTemplatesTool::class, ['search' => 'invo'])
        ->assertOk()
        ->assertSee('invoice')
        ->assertDontSee('welcome');
});

it('filters templates by tag', function () {
    Template::factory()->create(['slug' => 'tagged', 'tags' => ['transactional']]);
    Template::factory()->create(['slug' => 'untagged', 'tags' => ['marketing']]);

    TemplatesServer::tool(ListTemplatesTool::class, ['tag' => 'transactional'])
        ->assertOk()
        ->assertSee('tagged')
        ->assertDontSee('untagged');
});
