# AGENTS.md — andestr5

## Quick start

```bash
composer test          # phpunit --colors=always
vendor/bin/phpunit --display-phpunit-deprecations   # show PHPUnit 11 deprecation details
composer cs-check     # phpcs --colors -p
composer cs-fix       # phpcbf --colors -p
composer stan         # phpstan analyze (level 8, src/ only)
composer check        # test + cs-check
bin/cake server -p 8765  # dev server
```

Run a single test:

```bash
vendor/bin/phpunit tests/TestCase/Controller/EventosControllerTest.php
vendor/bin/phpunit --filter testIndex tests/TestCase/Controller/ItemsControllerTest.php
```

## Test quirks

- **DB**: SQLite via `DATABASE_TEST_URL=sqlite://./testdb.sqlite`. Schema loaded from `tests/schema.sql` at bootstrap (not migrations).
- **Fixtures** in `tests/Fixture/`. Defined via `protected array $fixtures = ['app.TableName'];`.
- **Controller tests** use `IntegrationTestTrait`. POST requires `enableCsrfToken()` + `enableSecurityToken()`.
- **Auth in tests**: Set session directly: `$this->session(['Auth' => $user]);` where `$user` is a `User` entity.
- **Active event**: `$this->session(['Auth' => $user, 'selected_evento_id' => 2]);`. This key gates most controller queries.
- **PHPUnit 11**: `@uses` in doc-comments is deprecated. Remove it (don't add `#[Uses]` attribute). Run `--display-phpunit-deprecations` to check.
- **Incomplete tests** in `Model/Table/*TableTest.php` are intentional stubs from bake.

## Architecture

CakePHP 5.1, Portuguese legislative voting tracker.

| Layer | Path | Key pattern |
|---|---|---|
| Controllers | `src/Controller/*Controller.php` | `$this->Authorization->skipAuthorization()` or `$this->Authorization->authorize($entity)` |
| Tables | `src/Model/Table/*Table.php` | CakePHP ORM table classes |
| Entities | `src/Model/Entity/*.php` | immutable-ish data objects |
| Policies | `src/Policy/*Policy.php` | `canMethod(IdentityInterface $user, $resource): bool`, role-based (admin/editor/relator) |
| Routes | `config/routes.php` | `DashedRoute`, fallbacks, root → `Eventos::index` |

- **Roles**: `admin`, `editor`, `relator`. Items ending in `.99` have special visibility rules for relators.
- **Session key** `selected_evento_id` drives which event's data is shown across all controllers.
- **PHPStan**: level 8, `src/` only, `treatPhpDocTypesAsCertain: false`.

## Style

- CakePHP coding standard (`cakephp/cakephp-codesniffer`)
- PHP 8.1+, strict types (`declare(strict_types=1)`)
- No `@uses` in test doc-comments
- No comments in code unless necessary
