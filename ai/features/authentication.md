# Authentication

MailCarrier has three distinct authentication surfaces. They are independent and serve different consumers.

| Surface | Who authenticates | Mechanism | Where it is configured |
| --- | --- | --- | --- |
| Admin panel (Filament) | A human operator | Session login: email + password, or Social OAuth (Socialite) | `config/mailcarrier.php` -> `social_auth_driver` |
| Mailing API (`POST /api/send`) | A backend service | Sanctum personal access token (`Authorization: Bearer <token>`) | `config/mailcarrier.php` -> `api_endpoint.auth_guard` |
| MCP server (`POST /mcp`) | An AI client (Claude, ChatGPT, Cursor, ...) | OAuth 2.1 via Laravel Passport (`auth:api`) | `config/mailcarrier.php` -> `mcp.middleware` |

## 1. Admin panel login

The Filament panel is the operator-facing dashboard. It supports two mutually exclusive modes:

- Email + password (default). Users are created with `php artisan mailcarrier:user`.
- Social OAuth, enabled by setting `MAILCARRIER_SOCIAL_AUTH_DRIVER` (configured through `php artisan mailcarrier:social`). When a driver is set, the login form is replaced by a single "Login" button that redirects to the OAuth provider. See `MailCarrier\Pages\Login` and `MailCarrier\Http\Controllers\SocialAuthController`.

The model behind the panel is `MailCarrier\Models\User`, resolved through the default (`web`) auth guard.

## 2. Mailing API tokens (Sanctum)

The `POST /api/send` endpoint is protected by Laravel Sanctum. Tokens are issued to a single internal "Auth Manager" user (`MailCarrier\Actions\Auth\EnsureAuthManagerExists`) and used as bearer tokens.

- Generate a token in the UI via the "API Tokens" resource (`MailCarrier\Resources\ApiTokenResource`), or with `php artisan mailcarrier:token`.
- Under the hood, `MailCarrier\Actions\Auth\GenerateToken` calls Sanctum's `createToken()` on the Auth Manager user.
- The guard is configurable via `MAILCARRIER_AUTH_GUARD` (defaults to `auth:sanctum`).

This flow is unchanged by the MCP integration and keeps using Sanctum.

## 3. MCP server authentication (OAuth 2.1 / Passport)

The MCP server (used by AI clients to inspect, create and edit templates) is protected by OAuth 2.1 implemented with Laravel Passport. This is the authentication mechanism described by the Model Context Protocol specification and is the one most widely supported by MCP clients.

### How a final user connects an MCP client

1. The MCP client discovers the OAuth endpoints exposed by `Mcp::oauthRoutes()` (registered in `routes/ai.php`) and dynamically registers itself. No manual client/secret creation is required.
2. The client redirects the user to MailCarrier to authorize access. The user authenticates with their **existing** admin login (email + password or Social OAuth) — the MCP flow reuses the same session login, so no new credentials are introduced.
3. The user approves the consent screen (the `mcp.authorize` / `mcp::authorize` Blade view, customisable via `php artisan vendor:publish --tag=mcp-views`).
4. Passport issues an access token with the single `mcp:use` scope. The client stores it and sends it on every MCP request as `Authorization: Bearer <token>`.

### Why a dedicated guard and model

The `User` model already uses Sanctum's `HasApiTokens` trait for the mailing API. Passport's `OAuthenticatable` contract requires `createToken`/`tokens`/`withAccessToken` with signatures that are incompatible with Sanctum's, and both token guards call `withAccessToken()` on the resolved user. To avoid that collision, MCP uses an isolated authenticatable:

- `MailCarrier\Mcp\Auth\OAuthUser` shares the `users` table but uses Passport's `HasApiTokens` trait and implements `OAuthenticatable`.
- A dedicated `api` guard (driver `passport`) backed by the `mcp_users` provider is registered automatically by `MailCarrier\MailCarrierServiceProvider` (only when `mailcarrier.mcp.enabled` is `true`).

This keeps the Sanctum mailing-API flow and the Passport MCP flow fully independent while authenticating against the same set of users.

### Configuration

```php
// config/mailcarrier.php
'mcp' => [
    'enabled' => env('MAILCARRIER_MCP_ENABLED', true),
    'path' => env('MAILCARRIER_MCP_PATH', 'mcp'),
    'middleware' => ['auth:api'], // Passport OAuth 2.1
],
```

Passport keys and migrations are set up automatically by `php artisan mailcarrier:install`. For stateless deployments (e.g. Docker), provide `PASSPORT_PRIVATE_KEY` / `PASSPORT_PUBLIC_KEY` instead of generated key files.

See also: [MCP server](mcp.md).
