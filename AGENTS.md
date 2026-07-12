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

- **DB**: SQLite via `DATABASE_TEST_URL=sqlite://./testdb.sqlite`. Schema loaded from `tests/schema.sql` at bootstrap (not migrations). Migrations exist in `config/Migrations/` but are unused in tests.
- **Fixtures** in `tests/Fixture/`. Defined via `protected array $fixtures = ['app.TableName'];`.
- **Controller tests** use `IntegrationTestTrait`. POST requires `enableCsrfToken()` + `enableSecurityToken()` (CSRF middleware is global in `Application::middleware()`).
- **Auth in tests**: Set session directly: `$this->session(['Auth' => $user]);` where `$user` is a `User` entity.
- **Active event**: `$this->session(['Auth' => $user, 'selected_evento_id' => 2]);`. This key gates most controller queries. Note: `AppController::beforeFilter` auto-sets `selected_evento_id` from `Eventos.ativo` DB field (source of truth; session is a mirror).
- **PHPUnit 11**: `@uses` in doc-comments is deprecated. Remove it (don't add `#[Uses]` attribute). Run `--display-phpunit-deprecations` to check.
- **Incomplete tests** in `Model/Table/*TableTest.php` are intentional stubs from bake.

## Architecture

CakePHP 5.1, Portuguese legislative voting tracker. Uses `cakephp/authentication` ^4.1 and `cakephp/authorization` ^3.5.

| Layer | Path | Key pattern |
|---|---|---|
| Controllers | `src/Controller/*Controller.php` | `$this->Authorization->skipAuthorization()` or `$this->Authorization->authorize($entity)` |
| Tables | `src/Model/Table/*Table.php` | CakePHP ORM table classes |
| Entities | `src/Model/Entity/*.php` | guarded mass-assignment via `$_accessible` |
| Policies | `src/Policy/*Policy.php` | `canMethod(IdentityInterface $user, $resource): bool`, role-based (admin/editor/relator) |
| Routes | `config/routes.php` | `DashedRoute`, fallbacks, root → `Eventos::index` |

- **Roles**: `admin`, `editor`, `relator`.
- **`.99` items**: relators cannot view/edit/delete other relators' `.99` items (enforced in `ItemPolicy` AND in `ItemsController::index` query).
- **Relator isolation in Votacoes**: relator's `grupo` is extracted from username pattern `grupoX` (e.g. `substr($username, 5)`). VotacoesController filters all queries to that grupo.
- **Session key** `selected_evento_id` drives which event's data is shown across all controllers. Auto-managed in `AppController::beforeFilter` from `Eventos.ativo` field (DB is source of truth; session mirrors it).
- **Filter persistence**: `votacoes_tr_filter`, `votacoes_grupo_filter` (VotacoesController), and `items_tr_filter` (ItemsController) persist in session across requests.
- **agentDebugLog**: `VotacoesController` writes JSON debug lines to `.cursor/debug-bc0914.log`. This file is in `.gitignore`.
- **PHPStan**: level 8, `src/` only, `treatPhpDocTypesAsCertain: false`, bootstrap via `config/bootstrap.php`. CI passes `SECURITY_SALT` env.
- **Psalm**: errorLevel 2, `src/` only, available but CI runs phpstan, not psalm.
- **Plugins loaded**: `Authentication`, `Authorization`, `DebugKit` (debug only), `Bake` (CLI only, optional), `Migrations` (CLI only). Defined in `config/plugins.php`.
- **app_local.php** is gitignored. Copy from `app_local.example.php`. DB is MySQL in dev/prod, SQLite in tests.

## Style

- CakePHP coding standard (`cakephp/cakephp-codesniffer`)
- PHP 8.1+, strict types (`declare(strict_types=1)`)
- No `@uses` in test doc-comments
- No comments in code unless necessary
