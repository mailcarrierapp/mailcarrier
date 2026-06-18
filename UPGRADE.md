# Upgrade Guide

## Upgrading from v2 to v3

MailCarrier v3 is a **major release** that modernizes the platform's foundations: it jumps straight from **Filament v3 to Filament v5** (and **Livewire v3 to v4**), raises the **PHP and Laravel** floors, and replaces the deprecated `spatie/data-transfer-object` dependency with a built-in DTO layer.

> **Note:** This single package major spans **two** Filament majors (v3 → v4 → v5). Because Filament recommends upgrading one major at a time, you run **both** Filament upgrade scripts in sequence (see Step 2). The package itself only ships once, as v3.

> **Estimated time:** 30–60 minutes for a standard install, longer if you have heavily customized Filament resources.

---

### Requirements

Before upgrading, make sure your application meets the new minimum requirements:

| Requirement | v2 | v3 |
| --- | --- | --- |
| PHP | `^8.1` | **`^8.4`** |
| Laravel | 10 / 11 / 12 | **13** |
| Filament | v3 | **v5** |
| Livewire | v3 | **v4** |
| Laravel Sanctum | v3 / v4 | **v4** |

If you cannot move to PHP 8.4 and Laravel 13 yet, stay on v2 until you can.

---

### Step 1 — Update your dependencies

Bump the package in your `composer.json`:

```json
{
    "require": {
        "php": "^8.4",
        "mailcarrier/mailcarrier": "^3.0"
    }
}
```

Then update:

```shell
composer update mailcarrier/mailcarrier --with-all-dependencies
```

Composer will pull in Filament v5, Livewire v4, Sanctum v4, and the Laravel 13 packages. If you have other Filament plugins installed, you may need to bump them to their Filament v5–compatible versions at the same time (note: a plugin's major often does **not** match Filament's — e.g. `pboivin/filament-peek` v4 targets Filament v5).

---

### Step 2 — Upgrade Filament (v3 → v4 → v5)

MailCarrier v3 runs on **Filament v5**. If your project customizes any Filament panels, resources, pages, widgets, or actions, upgrade **one major at a time** using Filament's automated scripts, following the official [3.x → 4.x](https://filamentphp.com/docs/4.x/upgrade-guide) and [4.x → 5.x](https://filamentphp.com/docs/5.x/upgrade-guide) upgrade guides.

First, v3 → v4:

```shell
composer require filament/upgrade:"^4.0" -W --dev
vendor/bin/filament-upgrade
```

Then, v4 → v5:

```shell
composer require filament/upgrade:"^5.0" -W --dev
vendor/bin/filament-v5
```

> The v5 script also checks that your PHP, Laravel, Livewire, and plugin versions are compatible, and prints the exact `composer require` commands for your Filament packages.

The most relevant changes that affect MailCarrier's own code (and likely yours) are summarized below. The first group applies to the v4 step; the [v5-specific changes](#filament-v5-specific-changes-livewire-v4) follow.

#### Forms are now "Schemas"

The `Filament\Forms\Form` object has been replaced by `Filament\Schemas\Schema`, and the `->form()` builder method is now `->schema()`.

```php
// v2 (Filament 3)
use Filament\Forms\Form;

public function form(Form $form): Form
{
    return $form->schema([
        // ...
    ]);
}
```

```php
// v3 (Filament 4)
use Filament\Schemas\Schema;

public function form(Schema $schema): Schema
{
    return $schema->components([
        // ...
    ]);
}
```

Actions that previously used `->form([...])` now use `->schema([...])`.

#### Actions namespace consolidation

Table and page actions now live under a single `Filament\Actions\*` namespace:

```php
// v2
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;

// v3
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
```

Tables now register row actions through `->recordActions()` instead of `->actions()`.

#### Layout components moved

Layout components such as `Grid`, `Section`, and `Actions` moved to `Filament\Schemas\Components\*`, and the form utilities (`Get`, `Set`) moved to `Filament\Schemas\Components\Utilities\*`:

```php
// v2
use Filament\Forms\Get;
use Filament\Forms\Components\Grid;

// v3
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Grid;
```

#### Width enum

The `MaxWidth` enum was replaced by `Width`, and string widths are now enum cases:

```php
// v2
use Filament\Support\Enums\MaxWidth;
$action->modalWidth('7xl');

// v3
use Filament\Support\Enums\Width;
$action->modalWidth(Width::SevenExtraLarge);
```

#### Auth pages moved

If you extend MailCarrier's login page, note the base classes moved namespaces:

```php
// v2
use Filament\Pages\Auth\Login as BaseLoginPage;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;

// v3
use Filament\Auth\Pages\Login as BaseLoginPage;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
```

#### Static properties are now instance properties

Several panel/widget properties (e.g. `$pollingInterval`) are no longer `static`. If you override them in a subclass, drop the `static` keyword.

#### Translation keys moved (`pages/auth/*` → `auth/pages/*`)

Filament v4 reorganized its panel translation files, swapping the `pages/auth` segment to `auth/pages`. If you reference any auth translation keys (e.g. when customizing the login page actions), update the namespace:

```php
// v2 (Filament 3)
__('filament-panels::pages/auth/login.form.actions.authenticate.label');

// v3 (Filament 4)
__('filament-panels::auth/pages/login.form.actions.authenticate.label');
```

A missed key renders as the raw, untranslated string (and, when used as a full-width form action label, makes the page look broken).

#### Rebuild your custom theme (Tailwind v4)

This is the most common cause of an **unstyled panel** after upgrading. Filament v4 uses **Tailwind CSS v4**, which is CSS-first (no `tailwind.config.js` preset). If you registered a custom theme with `->theme(asset(...))`, the v3-compiled CSS will not work on v4 and the entire panel (including the login page) will render without styles.

If you maintain your own theme, migrate it to the v4 format:

- Replace the `content` array / `@config` directive with `@source` directives in your theme CSS, and port any `safelist` to `@source inline("...")`.
- Use the `@tailwindcss/vite` plugin and `tailwindcss@^4` instead of the v3 PostCSS toolchain.
- Follow the [official Filament v4 theme guide](https://filamentphp.com/docs/4.x).

MailCarrier's bundled theme is already migrated. You only need to **rebuild and republish the assets** so the new theme reaches your `public/` directory:

```shell
php artisan vendor:publish --tag="mailcarrier-assets" --force
php artisan filament:optimize-clear
```

#### Re-publish overridden Filament/plugin views

If you published and customized any MailCarrier or plugin views, re-publish them. For example, MailCarrier's `filament-peek` preview-modal override changed: the removed `\Pboivin\FilamentPeek\Support\View` helper is now the `\Pboivin\FilamentPeek\Facades\Peek` facade (`isPreviewModalRegistered()` / `isBuilderPreviewRegistered()`). A `Class "...Support\View" not found` error means a stale view override needs re-publishing.

<h4 id="filament-v5-specific-changes-livewire-v4">Filament v5-specific changes (Livewire v4)</h4>

The v4 → v5 step is comparatively small for this package because the schema/actions/Tailwind work was already done in the v4 step. The notable changes:

- **Livewire v4 component names drop the `::` namespace.** Registered component names and `@livewire(...)` / `<livewire:...>` references must use plain hyphenated names. If you register or reference MailCarrier/plugin Livewire components, rename them:

  ```php
  // Filament v4 (Livewire v3)
  Livewire::component('filament-peek::builder-editor', PreviewBuilderEditor::class);

  // Filament v5 (Livewire v4)
  Livewire::component('filament-peek-builder-editor', PreviewBuilderEditor::class);
  ```

  Likewise in Blade: `@livewire('filament-peek::builder-editor')` → `@livewire('filament-peek-builder-editor')`. A missing-component error after upgrading is almost always a leftover `::` name.

- **Re-run the theme build.** The theme remains Tailwind v4, but rebuild and republish assets so the CSS matches Filament v5's component classes (same commands as above).

- **Action `setUp()` evaluates eagerly.** When customizing an action, compute schema defaults and any record-dependent values **lazily** (pass a closure), because `$this->getRecord()` is `null` while the action is being set up:

  ```php
  // Breaks: getRecord() is null at setUp() time
  ->default(SomeHelper::make($this->getRecord())->values())

  // Works: evaluated when the form renders
  ->default(fn (): array => SomeHelper::make($this->getRecord())->values())
  ```

---

### Step 3 — Migrate from `spatie/data-transfer-object`

MailCarrier no longer depends on `spatie/data-transfer-object`. All DTOs (`GenericMailDto`, `SendMailDto`, `ContactDto`, `RecipientDto`, `LogTemplateDto`, `RemoteAttachmentDto`, etc.) now extend an **in-house** `MailCarrier\Dto\DataTransferObject` base class.

For most users this is transparent — construction, `toArray()`, `toJson()`, casters, and validation all behave the same. **You only need to act if your code references Spatie's classes directly.**

#### If you reference Spatie types in middleware or custom code

The `sending` / `beforeSending` middleware closures receive the same MailCarrier DTOs as before. If you imported anything from the `Spatie\DataTransferObject\*` namespace, switch to the MailCarrier equivalents:

| Removed (Spatie) | Replacement (MailCarrier) |
| --- | --- |
| `Spatie\DataTransferObject\DataTransferObject` | `MailCarrier\Dto\DataTransferObject` |
| `Spatie\DataTransferObject\Caster` | `MailCarrier\Dto\Contracts\Caster` |
| `Spatie\DataTransferObject\Casters\ArrayCaster` | `MailCarrier\Dto\Casters\ArrayCaster` |
| `Spatie\DataTransferObject\Attributes\CastWith` | `MailCarrier\Dto\Attributes\CastWith` |
| `Spatie\DataTransferObject\Attributes\Strict` | `MailCarrier\Dto\Attributes\Strict` |
| `Spatie\DataTransferObject\Validator` | `MailCarrier\Dto\Contracts\Validator` |
| `Spatie\DataTransferObject\Validation\ValidationResult` | `MailCarrier\Dto\Validators\ValidationResult` |

#### Custom casters

If you wrote a custom caster, update the implemented interface and the `cast()` signature:

```php
// v2
use Spatie\DataTransferObject\Caster;

class MyCaster implements Caster
{
    public function cast(mixed $value): mixed
    {
        // ...
    }
}
```

```php
// v3
use MailCarrier\Dto\Contracts\Caster;

class MyCaster implements Caster
{
    public function cast(mixed $value): mixed
    {
        // ...
    }
}
```

#### Custom validators

```php
// v2
use Spatie\DataTransferObject\Validator;
use Spatie\DataTransferObject\Validation\ValidationResult;

// v3
use MailCarrier\Dto\Contracts\Validator;
use MailCarrier\Dto\Validators\ValidationResult;
```

#### New `with()` helper

DTOs now expose a `with()` method that returns a copy with selected properties overridden. This is handy inside the `sending` middleware when you want to derive a new value instead of mutating in place:

```php
MailCarrier::sending(function (GenericMailDto $mail, Closure $next): void {
    if ($mail->sender && is_null($mail->sender->name)) {
        // Reconstruct (still supported)
        $mail->sender = new ContactDto([
            'email' => $mail->sender->email,
            'name' => 'MailCarrier',
        ]);

        // Or, more concisely:
        $mail->sender = $mail->sender->with(name: 'MailCarrier');
    }

    $next($mail);
});
```

Mutating DTO properties directly inside the middleware remains fully supported.

---

### Step 4 — Run your test suite

After upgrading, clear caches and run your tests:

```shell
php artisan config:clear
php artisan view:clear
composer test
```

Re-publish the assets so the rebuilt Filament v5 theme reaches your `public/` directory (required — see "Rebuild your custom theme" above):

```shell
php artisan vendor:publish --tag="mailcarrier-assets" --force
php artisan filament:optimize-clear
```

If you publish and override MailCarrier views or config, re-publish them to pick up any changes:

```shell
php artisan vendor:publish --tag="mailcarrier-config" --force
php artisan vendor:publish --tag="mailcarrier-views" --force
```

> Re-publishing with `--force` overwrites your local copies. Diff them first if you have customizations.

---

### Summary checklist

- [ ] Application is on **PHP 8.4+** and **Laravel 13**.
- [ ] `mailcarrier/mailcarrier` bumped to `^3.0` and `composer update -W` completed.
- [ ] Filament upgraded **v3 → v4 → v5** (ran both `filament-upgrade` and `filament-v5` if you customize Filament).
- [ ] Livewire upgraded to **v4**; component names migrated off the `::` namespace.
- [ ] Filament auth translation keys updated (`pages/auth/*` → `auth/pages/*`) if referenced.
- [ ] Custom theme rebuilt for Tailwind v4 (if you maintain one) and assets re-published.
- [ ] Any references to `Spatie\DataTransferObject\*` replaced with `MailCarrier\Dto\*`.
- [ ] Custom casters/validators updated to the new interfaces.
- [ ] Config/views re-published if customized (stale plugin view overrides re-published).
- [ ] Test suite passes.
