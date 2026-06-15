# Upgrade Guide

## Upgrading from v2 to v3

MailCarrier v3 is a **major release** that modernizes the platform's foundations: it moves to **Filament v4**, raises the **PHP and Laravel** floors, and replaces the deprecated `spatie/data-transfer-object` dependency with a built-in DTO layer.

> **Estimated time:** 15–30 minutes for a standard install, longer if you have heavily customized Filament resources.

---

### Requirements

Before upgrading, make sure your application meets the new minimum requirements:

| Requirement | v2 | v3 |
| --- | --- | --- |
| PHP | `^8.1` | **`^8.4`** |
| Laravel | 10 / 11 / 12 | **13** |
| Filament | v3 | **v4** |
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

Composer will pull in Filament v4, Sanctum v4, and the Laravel 13 packages. If you have other Filament plugins installed, you may need to bump them to their Filament v4–compatible versions at the same time.

---

### Step 2 — Upgrade Filament to v4

MailCarrier v3 runs on **Filament v4**. If your project customizes any Filament panels, resources, pages, widgets, or actions, follow the official [Filament 3.x → 4.x upgrade guide](https://filamentphp.com/docs/4.x/upgrade-guide). Filament ships an automated upgrade script that handles the majority of namespace changes:

```shell
composer require filament/upgrade:"^4.0" -W --dev
vendor/bin/filament-upgrade
```

The most relevant changes that also affect MailCarrier's own code (and likely yours) are summarized below.

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
- [ ] Filament upgraded to **v4** (ran `filament-upgrade` if you customize Filament).
- [ ] Any references to `Spatie\DataTransferObject\*` replaced with `MailCarrier\Dto\*`.
- [ ] Custom casters/validators updated to the new interfaces.
- [ ] Config/views re-published if customized.
- [ ] Test suite passes.
