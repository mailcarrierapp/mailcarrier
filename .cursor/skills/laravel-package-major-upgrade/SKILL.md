---
name: laravel-package-major-upgrade
description: >-
  Bumps a Laravel package to support a new Laravel major in composer.json
  (illuminate/*, orchestra/testbench, Pest) and extends CI matrix in
  .github/workflows/run-tests.yml. Use when the user asks to add support for a
  new Laravel version, upgrade Laravel compatibility, or align Testbench/Pest
  with a new framework major.
---

# Laravel package major upgrade

When adding support for a **new Laravel major** (e.g. “Laravel 13”), treat the user’s number as the **framework major only**. **Do not** assume `orchestra/testbench` or `pestphp/*` majors match Laravel’s number.

## 1. Resolve the target

- From the user message, take the **Laravel major** `N` (e.g. “13” → `N = 13`).
- Read `composer.json` and `.github/workflows/run-tests.yml` to match existing constraint and YAML style (pipe-separated `^` ranges, matrix ordering, `include` / `exclude` patterns).

## 2. `composer.json` — `illuminate/*`

- Find every dependency under `illuminate/` (e.g. `illuminate/contracts`, `illuminate/database`, `illuminate/support` if present).
- Extend each version constraint with the **same pattern already used** (typically `|^N.0` appended to an existing chain like `^10.0|^11.0|...`).
- Do **not** change unrelated packages unless the upgrade requires it (e.g. Filament, Sanctum) — scope is framework + dev test stack unless the user expands it.

## 3. `composer.json` — `orchestra/testbench`

- **Do not** set Testbench’s major from `N` (Laravel 13 ≠ Testbench 13).
- Read the **current highest** Testbench major in `require-dev` (e.g. `^8.0|^9.0|^10.0|^11.0` → highest is `11`).
- For a **new** Laravel line, add the **next** Testbench major: append `|^M.0` where `M = previous_max + 1`.
- If unsure whether `M` is correct for that Laravel release, verify with [orchestra/testbench](https://github.com/orchestra/testbench) releases/README or library docs (e.g. Context7) before finalizing.

## 4. `composer.json` — Pest (if present)

- If `pestphp/pest` and plugins (`pestphp/pest-plugin-laravel`, `pestphp/pest-plugin-faker`, etc.) use piped majors, append the **next** major for each, consistent with the existing chain (same rule as Testbench: **increment from the package’s previous major**, not from Laravel `N`).
- If the repo uses a single major or no pipes, follow the same **increment-by-one-major** idea or consult the [Laravel upgrade guide](https://laravel.com/docs/upgrade) and [Pest](https://pestphp.com/docs) release notes for the target Laravel version.

## 5. `.github/workflows/run-tests.yml`

- **`matrix.laravel`**: add a new entry for the new line, using the same constraint style as existing rows (e.g. `"^N.0"`). Keep project ordering (often newest first).
- **`matrix.include`**: for each Laravel row that needs a pinned Testbench constraint for `composer require`, add:

  ```yaml
  - laravel: "^N.0"
    testbench: M.*
  ```

  where `M.*` matches the **Testbench major** chosen in step 3 (same mapping the workflow already uses for other versions).
- **`matrix.exclude`**: mirror Laravel’s **minimum PHP** for `N` (copy patterns from Laravel docs or existing rows for other majors). Do not drop excludes that still apply to older matrix entries.
- If the job uses `composer require "laravel/framework:${{ matrix.laravel }}"` and a Testbench line, ensure the install step still resolves (some workflows use `testbench` matrix key — keep consistency with this repo).

## 6. Validate

- Run `composer update` (or the narrowest install that matches the new matrix cell) and `composer test` (and `composer stan` / `composer cs-fix` if the project defines them).
- Fix any breakages **only** as needed for the new major; avoid unrelated refactors.

## Quick checklist

- [ ] All `illuminate/*` constraints include `^N.0` (or project-equivalent).
- [ ] `orchestra/testbench` gained **one new major** derived from the previous max, verified against Testbench’s Laravel compatibility if needed.
- [ ] Pest packages updated in lockstep if present.
- [ ] `run-tests.yml` matrix + `include` (+ `exclude` if needed) match the new Laravel/Testbench pair.

For a historical Testbench ↔ Laravel cheat sheet (verify at upgrade time), see [reference.md](reference.md).
