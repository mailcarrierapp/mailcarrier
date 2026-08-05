<?php

use Illuminate\Support\Facades\Mail;
use MailCarrier\Mail\GenericMail;
use MailCarrier\Mcp\Servers\TemplatesServer;
use MailCarrier\Mcp\Tools\SendTestEmailTool;
use MailCarrier\Models\Log;
use MailCarrier\Models\Template;
use function Pest\Laravel\assertDatabaseCount;

beforeEach(fn () => Mail::fake());

it('sends a test email for a template', function () {
    Template::factory()->create([
        'slug' => 'welcome',
        'layout_id' => null,
        'content' => '<p>Hello {{ name }}</p>',
    ]);

    TemplatesServer::tool(SendTestEmailTool::class, [
        'slug' => 'welcome',
        'email' => 'tester@example.org',
        'variables' => ['name' => 'Danilo'],
    ])
        ->assertOk()
        ->assertSee('tester@example.org');

    Mail::assertSent(GenericMail::class, 1);
});

it('does not store a log for test emails', function () {
    Template::factory()->create([
        'slug' => 'welcome',
        'layout_id' => null,
        'content' => '<p>Hi</p>',
    ]);

    TemplatesServer::tool(SendTestEmailTool::class, [
        'slug' => 'welcome',
        'email' => 'tester@example.org',
    ])->assertOk();

    assertDatabaseCount(Log::class, 0);
});

it('errors when the template is not found', function () {
    TemplatesServer::tool(SendTestEmailTool::class, [
        'slug' => 'missing',
        'email' => 'tester@example.org',
    ])->assertHasErrors(['No template found']);

    Mail::assertNothingSent();
});

it('errors when the email is invalid', function () {
    Template::factory()->create(['slug' => 'welcome']);

    TemplatesServer::tool(SendTestEmailTool::class, [
        'slug' => 'welcome',
        'email' => 'not-an-email',
    ])->assertHasErrors();

    Mail::assertNothingSent();
});
