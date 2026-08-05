<?php

use MailCarrier\Mcp\Servers\TemplatesServer;
use MailCarrier\Mcp\Tools\CreateTemplateTool;
use MailCarrier\Models\Template;
use MailCarrier\Models\User;
use function Pest\Laravel\assertDatabaseHas;

it('creates a template and auto-generates the slug', function () {
    TemplatesServer::tool(CreateTemplateTool::class, [
        'name' => 'Welcome email',
        'content' => '<p>Hello</p>',
    ])
        ->assertOk()
        ->assertSee('welcome-email');

    assertDatabaseHas(Template::class, [
        'name' => 'Welcome email',
        'slug' => 'welcome-email',
        'content' => '<p>Hello</p>',
    ]);
});

it('creates a template with the given slug, tags and description', function () {
    TemplatesServer::tool(CreateTemplateTool::class, [
        'name' => 'Invoice',
        'slug' => 'custom-invoice',
        'content' => '<p>Invoice</p>',
        'description' => 'Sent after a purchase',
        'tags' => ['transactional'],
    ])->assertOk();

    $template = Template::query()->where('slug', 'custom-invoice')->firstOrFail();

    expect($template->description)->toBe('Sent after a purchase')
        ->and($template->tags)->toBe(['transactional']);
});

it('associates the authenticated user as the author', function () {
    /** @var User $user */
    $user = User::factory()->create();

    TemplatesServer::actingAs($user)
        ->tool(CreateTemplateTool::class, [
            'name' => 'Authored',
            'content' => '<p>Authored</p>',
        ])
        ->assertOk();

    assertDatabaseHas(Template::class, [
        'slug' => 'authored',
        'user_id' => $user->id,
    ]);
});

it('errors when required fields are missing', function () {
    TemplatesServer::tool(CreateTemplateTool::class, ['name' => 'No content'])
        ->assertHasErrors();
});
