# MailCarrier v2 → v3 — Agent Upgrade Instructions

You are an AI coding agent upgrading a Laravel application that depends on the `mailcarrier/mailcarrier` package from v2 to v3. Follow these steps **in order**. Run the verification gate after each phase. **Stop and report** if any stop condition is hit. Do not skip steps. Do not invent file paths — search the project to confirm a file exists before editing it.

## Context (what changes in v3)

- Minimum PHP raised to **8.4** (was 8.1).
- Laravel required version is now **13** only (was 10/11/12).
- Filament upgraded from **v3 to v5** (this package major spans two Filament majors; run both upgrade scripts in sequence).
- Livewire upgraded from **v3 to v4**.
- Laravel Sanctum required version is now **v4** only.
- The dependency `spatie/data-transfer-object` is **removed**; MailCarrier now ships an in-house DTO layer under the `MailCarrier\Dto` namespace.

## Preconditions — verify before doing anything

1. Confirm the project currently requires `mailcarrier/mailcarrier` at a `^2.x` version in `composer.json`. If it is already `^3`, **stop**: nothing to do.
2. Confirm `php -v` reports **8.4 or higher**. If lower, **stop and report**: "Upgrade PHP to 8.4+ before continuing."
3. Confirm the project is on Laravel 13, or can be upgraded to it. If the app is pinned below Laravel 13 and cannot move, **stop and report**.
4. Ensure the working tree is clean / committed so the upgrade can be reviewed as a diff.

## Phase 1 — Dependencies

1. In the project `composer.json`, set:
   - `"php": "^8.4"`
   - `"mailcarrier/mailcarrier": "^3.0"`
2. If the project directly requires any of these, align them (skip ones not present):
   - `"laravel/framework": "^13.0"`
   - `"laravel/sanctum": "^4.0"`
   - `"filament/filament": "^5.0"`
   - `"livewire/livewire": "^4.0"`
3. Run:

   ```shell
   composer update mailcarrier/mailcarrier --with-all-dependencies
   ```

**Stop condition:** If Composer reports an unresolvable conflict caused by another Filament plugin still requiring an older Filament major, report the conflicting package(s) and ask the user how to proceed (usually: bump that plugin to its Filament v5 release). Note a plugin's major often does **not** match Filament's (e.g. `pboivin/filament-peek` v4 targets Filament v5). Do not force-downgrade Filament.

**Verification gate:** `composer update` completes and `vendor/mailcarrier/mailcarrier` is at `^3`.

## Phase 2 — Filament migration (v3 → v4 → v5)

If the project does **not** extend or customize any MailCarrier/Filament classes (no custom Resources, Pages, Widgets, Actions, or Schemas), skip to Phase 3.

Otherwise run Filament's automated upgrade tools, **one major at a time, in this order**. First v3 → v4:

```shell
composer require filament/upgrade:"^4.0" -W --dev
vendor/bin/filament-upgrade
```

Then v4 → v5 (the script accepts the source directory as an argument to skip the prompt; for a package, pass `src`):

```shell
composer require filament/upgrade:"^5.0" -W --dev
vendor/bin/filament-v5 app   # or: vendor/bin/filament-v5 src
```

Then manually resolve anything the tools missed, applying these exact transformations across the project's `app/` (and any published MailCarrier overrides). Items 1–6 are the v4 changes; items 10–12 are v5/Livewire v4 specific:

1. **Form → Schema.** Replace `Filament\Forms\Form` with `Filament\Schemas\Schema`. Method signatures `form(Form $form): Form` become `form(Schema $schema): Schema`. Calls to `->schema([...])` that built a form's root become `->components([...])`. Action/builder `->form([...])` becomes `->schema([...])`.
2. **Actions namespace.** Replace `Filament\Tables\Actions\Action` and `Filament\Tables\Actions\ActionGroup` with `Filament\Actions\Action` and `Filament\Actions\ActionGroup`. Tables register row actions via `->recordActions([...])` instead of `->actions([...])`.
3. **Layout components.** Move `Grid`, `Section`, `Actions` imports to `Filament\Schemas\Components\*`. Move `Get`/`Set` to `Filament\Schemas\Components\Utilities\*` (was `Filament\Forms\Get` / `Filament\Forms\Set`).
4. **Width enum.** Replace `Filament\Support\Enums\MaxWidth` with `Filament\Support\Enums\Width`; convert string widths to enum cases (e.g. `'7xl'` → `Width::SevenExtraLarge`, `'2xl'` → `Width::TwoExtraLarge`).
5. **Auth pages.** Replace `Filament\Pages\Auth\Login` with `Filament\Auth\Pages\Login`, and `Filament\Http\Responses\Auth\Contracts\LoginResponse` with `Filament\Auth\Http\Responses\Contracts\LoginResponse`.
6. **Static → instance props.** Remove the `static` keyword from overridden panel/widget properties that are no longer static (e.g. `$pollingInterval`, navigation properties whose type changed to `string|\BackedEnum|null`).
7. **Translation keys.** The panel auth translation namespace swapped `pages/auth` → `auth/pages`. Search for `filament-panels::pages/auth` and rewrite to `filament-panels::auth/pages` (e.g. `filament-panels::pages/auth/login.form.actions.authenticate.label` → `filament-panels::auth/pages/login.form.actions.authenticate.label`). A missed key renders as the raw string and looks like a broken page.
8. **Custom theme (Tailwind v4) — common cause of an unstyled panel.** If the project registers a custom theme via `->theme(asset(...))`, the v3-compiled CSS will not work on Filament v4 (Tailwind v4 is CSS-first; the `tailwind.config.js` preset no longer exists). Migrate the theme CSS: replace the `content` array / `@config` with `@source` directives, port any `safelist` to `@source inline("...")`, and build with the `@tailwindcss/vite` plugin + `tailwindcss@^4`. Then rebuild assets. If the project only uses MailCarrier's bundled theme, no theme code change is needed — just re-publish assets (see below).
9. **Stale published view overrides.** If the project published/overrode MailCarrier or plugin views, a `Class "...Support\View" not found` (filament-peek) or similar error means a stale override. Re-publish the views, or update the helper: `\Pboivin\FilamentPeek\Support\View` → `\Pboivin\FilamentPeek\Facades\Peek` (`isPreviewModalRegistered()` / `isBuilderPreviewRegistered()`).
10. **(v5 / Livewire v4) Component names drop the `::` namespace.** Search for `Livewire::component('...::...'`, `@livewire('...::...')`, and `<livewire:...::...>` and rename to plain hyphenated names. Example: `filament-peek::builder-editor` → `filament-peek-builder-editor`. A missing-component error after upgrade is almost always a leftover `::` name.
11. **(v5) Rebuild the theme.** The theme stays Tailwind v4; rebuild and re-publish assets so the CSS matches Filament v5 component classes (commands below).
12. **(v5) Lazy evaluation in action `setUp()`.** In custom actions, wrap record-dependent values (e.g. schema field `->default(...)`) in a closure: `$this->getRecord()` is `null` during `setUp()`. Use `->default(fn () => ...)` instead of computing the value eagerly.

For anything ambiguous, consult the official Filament [3→4](https://filamentphp.com/docs/4.x/upgrade-guide) and [4→5](https://filamentphp.com/docs/5.x/upgrade-guide) upgrade guides rather than guessing.

Re-publish MailCarrier assets so the Filament v5 theme reaches `public/`:

```shell
php artisan vendor:publish --tag="mailcarrier-assets" --force
php artisan filament:optimize-clear
```

**Verification gate:** the app boots (`php artisan about` runs without class-not-found / type errors), `php artisan filament:optimize-clear` succeeds, and the panel renders **styled** (no raw `filament-panels::...` translation strings, login page has its normal layout).

## Phase 3 — Replace `spatie/data-transfer-object` references

This only matters if the project references Spatie's DTO classes directly (commonly inside a `MailCarrier::sending(...)` or `MailCarrier::beforeSending(...)` middleware closure, or in custom casters/validators).

1. Search the project for `Spatie\DataTransferObject`. If there are no matches, skip this phase.
2. Apply this import mapping:

   | Old (remove) | New (use) |
   | --- | --- |
   | `Spatie\DataTransferObject\DataTransferObject` | `MailCarrier\Dto\DataTransferObject` |
   | `Spatie\DataTransferObject\Caster` | `MailCarrier\Dto\Contracts\Caster` |
   | `Spatie\DataTransferObject\Casters\ArrayCaster` | `MailCarrier\Dto\Casters\ArrayCaster` |
   | `Spatie\DataTransferObject\Attributes\CastWith` | `MailCarrier\Dto\Attributes\CastWith` |
   | `Spatie\DataTransferObject\Attributes\Strict` | `MailCarrier\Dto\Attributes\Strict` |
   | `Spatie\DataTransferObject\Validator` | `MailCarrier\Dto\Contracts\Validator` |
   | `Spatie\DataTransferObject\Validation\ValidationResult` | `MailCarrier\Dto\Validators\ValidationResult` |

3. Custom caster signature is unchanged: `public function cast(mixed $value): mixed`. Only the implemented interface import changes.
4. DTO construction (positional array `new ContactDto([...])` and named args `new ContactDto(email: ...)`), `toArray()`, `toJson()`, casters, and validators all behave the same. Do **not** rewrite working construction code.
5. Optional improvement: a new `with()` method returns a modified copy, e.g. `$mail->sender = $mail->sender->with(name: 'MailCarrier');`. Direct property mutation inside middleware is still supported — do not force this change.

**Verification gate:** `grep -r "Spatie\\\\DataTransferObject" app/` returns nothing.

## Phase 4 — Re-publish assets (only if customized)

If the project published MailCarrier config or views, re-publish to pick up changes. **Warn the user** that `--force` overwrites local customizations; diff first.

```shell
php artisan vendor:publish --tag="mailcarrier-config" --force
php artisan vendor:publish --tag="mailcarrier-views" --force
```

## Final verification

Run, in order, and report results:

```shell
php artisan config:clear
php artisan view:clear
composer test
```

If the project has static analysis configured, also run it (e.g. `composer analyse` / `vendor/bin/phpstan analyse`) and resolve any new Laravel 13 deprecations (notably `Illuminate\Foundation\Http\Middleware\VerifyCsrfToken` → `PreventRequestForgery` if referenced).

## Completion report

Summarize: the dependency versions installed, files changed, any Spatie references migrated, and the test/analysis results. Flag anything you could not resolve and left for the user.
