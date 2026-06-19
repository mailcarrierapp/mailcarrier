# MCP server

MailCarrier ships a [Model Context Protocol](https://modelcontextprotocol.io) server built on the official [`laravel/mcp`](https://laravel.com/docs/12.x/mcp) package. It lets AI clients (Claude, ChatGPT, Cursor, etc.) inspect, create and edit email templates over an authenticated HTTP transport.

## Overview

- Server class: `MailCarrier\Mcp\Servers\TemplatesServer` (`Laravel\Mcp\Server`).
- Transport: web (HTTP POST). Registered in `routes/ai.php` via `Mcp::web(...)`.
- Authentication: OAuth 2.1 via Laravel Passport (`auth:api`). See [authentication](authentication.md).
- Scope: email templates only.

## Endpoint and configuration

The server is exposed at the configured path (default `/mcp`) and can be toggled and customised through `config/mailcarrier.php`:

```php
'mcp' => [
    'enabled' => env('MAILCARRIER_MCP_ENABLED', true),
    'path' => env('MAILCARRIER_MCP_PATH', 'mcp'),
    'middleware' => ['auth:api'],
],
```

Set `MAILCARRIER_MCP_ENABLED=false` to completely disable the server (no routes, guard, or consent screen are registered).

## Available tools

All tools live in `MailCarrier\Mcp\Tools`. Read-only tools are annotated accordingly so clients can reason about side effects.

| Tool | Purpose | Key inputs | Output |
| --- | --- | --- | --- |
| `ListTemplatesTool` (read-only) | List templates without their content | `search?`, `tag?` | `count` + array of `{ id, slug, name, description, tags, layout, is_locked }` |
| `GetTemplateTool` (read-only) | Inspect a single template, including its Twig content | `slug` or `id` | Full template incl. `content` and `layout` |
| `CreateTemplateTool` | Create a new template | `name`, `content`, `slug?`, `description?`, `tags?`, `layout_id?` | `{ id, slug, name }` |
| `UpdateTemplateTool` | Edit an existing template | `slug` or `id`, plus any of `name`, `content`, `slug`, `description`, `tags`, `layout_id` | `{ id, slug, name }` |
| `RenderTemplateTool` (read-only) | Render a template with variables to preview the HTML | `slug` or `id`, `variables?` | Rendered HTML text |
| `SendTestEmailTool` | Send a test email rendered from a template to one recipient (not logged) | `slug` or `id`, `email`, `subject?`, `variables?`, `enqueue?` | Confirmation text |

Notes:

- Template content uses the [Twig](https://twig.symfony.com) syntax and may extend a layout via `layout_id`.
- If `slug` is omitted on create, it is auto-generated from `name` (`MailCarrier\Actions\Templates\GenerateSlug`).
- Locked templates (`is_locked = true`) cannot be edited; `UpdateTemplateTool` returns an error.
- `SendTestEmailTool` mirrors the panel's "Send test" action: it sends through `SendMail` with logging disabled, so test emails do not appear in the logs.
- Tools reuse the existing domain actions (`FindBySlug`, `GenerateSlug`, `Render`, `SendMail`) and the `TemplateObserver` cache invalidation, so behaviour matches the admin panel.

## Connecting a client

1. Point your MCP client at `https://<your-domain>/mcp`.
2. Complete the OAuth 2.1 flow (the client registers itself and you approve the consent screen after logging in with your normal MailCarrier admin credentials). See [authentication](authentication.md).
3. The client sends `Authorization: Bearer <token>` on each request.

## Inspecting the server

Use the bundled MCP Inspector to verify the server, authentication and tools:

```shell
php artisan mcp:inspector mcp
```

When the server is protected by `auth:api`, supply a valid bearer token in the inspector's headers.

## Testing

Tool behaviour is covered by Pest tests in `tests/Feature/Mcp/`, invoking primitives directly through the package's testing API, e.g.:

```php
use MailCarrier\Mcp\Servers\TemplatesServer;
use MailCarrier\Mcp\Tools\CreateTemplateTool;

TemplatesServer::actingAs($user)
    ->tool(CreateTemplateTool::class, ['name' => 'Welcome', 'content' => '<p>Hi</p>'])
    ->assertOk();
```
