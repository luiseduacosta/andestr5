# Code Wiki

## 1. Project Summary

This repository is a CakePHP 5 application for managing events, support texts, agenda items, users, GTs (working groups), and voting records. The application is server-rendered and follows CakePHP's standard MVC organization:

- `src/Controller`: HTTP request orchestration
- `src/Model/Table`: ORM tables, associations, validation, and custom finders
- `src/Model/Entity`: record shapes and entity mutators
- `src/Policy`: authorization rules
- `templates`: PHP views for HTML rendering
- `config`: application bootstrap, routing, datasource, and framework configuration
- `tests`: controller/model/policy tests and test database bootstrap

The business center of the app is the voting workflow for items grouped by event and TR.

## 2. Tech Stack

- PHP `>= 8.1`
- CakePHP `5.1`
- CakePHP Authentication plugin
- CakePHP Authorization plugin
- CakePHP Migrations plugin
- PHPUnit for tests
- PHP_CodeSniffer, PHPStan, and Psalm for code quality

Dependency definitions live in `composer.json`.

## 3. Repository Layout

```text
.
|- bin/                  # Cake CLI entrypoints
|- config/               # Bootstrap, routes, app config, migrations
|- docs/                 # This wiki
|- src/
|  |- Command/           # CLI commands (e.g. FixEncodingCommand)
|  |- Console/           # Installer
|  |- Controller/        # MVC controllers
|  |- Model/
|  |  |- Entity/         # ORM entities
|  |  `- Table/          # ORM tables and validation
|  |- Policy/            # Authorization policies
|  |- View/              # View classes, helpers, cells
|  `- Application.php    # App bootstrap and middleware
|- templates/            # Server-rendered views
|- tests/                # PHPUnit tests, fixtures, schema bootstrap
|- webroot/              # Public document root
|- composer.json         # PHP dependencies and scripts
`- README.md            # Generic CakePHP skeleton instructions
```

## 4. High-Level Architecture

### 4.1 Request Lifecycle

1. Requests enter through `webroot/index.php`.
2. `src/Application.php` bootstraps the app and builds the middleware queue.
3. `config/routes.php` maps the URL to a controller action.
4. A controller in `src/Controller` coordinates ORM queries and authorization.
5. Table classes in `src/Model/Table` load and validate data.
6. Entities in `src/Model/Entity` represent records and apply light data behavior.
7. Templates in `templates/` render the response.

### 4.2 Middleware Pipeline

`src/Application.php` registers middleware in this order:

1. Error handler
2. Asset middleware
3. Routing middleware
4. Authentication middleware
5. Authorization middleware
6. Body parser middleware
7. CSRF protection middleware

This means authentication and authorization run before controllers execute, while request body parsing and CSRF protection are globally available.

### 4.3 Architectural Characteristics

- Server-rendered MVC app, not a JSON API
- Convention-over-configuration CakePHP structure
- ORM-based relational domain model
- Role-based authorization via policy classes
- Database-driven "active event" selection using the `ativo` boolean field as the source of truth, synchronized to session
- CRUD controllers plus a custom multi-step voting workflow
- Persistent TR and Grupo filter state via session across Votacoes and Items screens

## 5. Core Application Flow

### 5.1 Bootstrap and Configuration

- `config/bootstrap.php` loads `app.php`, then optional `app_local.php`
- Environment-variable loading via `.env` is present but commented out by default
- Datasources, cache, mailer, log, and security salt are configured centrally
- An irregular inflector rule maps `votacao` to `votacoes`
- Mobile/tablet detectors are registered through `mobiledetect`

### 5.2 Routing

`config/routes.php` keeps routing simple:

- `/` -> `EventosController::index`
- `/pages/*` -> `PagesController::display`
- Fallback routes enabled for conventional controller/action URLs

Because fallbacks are enabled, most controller actions are reachable through CakePHP's default route conventions.

### 5.3 Shared Controller Behavior

`src/Controller/AppController.php` is the shared controller base. Its main responsibilities are:

- Loading `Flash`, `Authentication`, and `Authorization` components
- In `beforeFilter()`: querying the database for the event with `ativo = true` and writing its ID to the `selected_evento_id` session key. If no active event exists and no session value is present, it falls back to the last event by `ordem DESC`.
- In `beforeRender()`: re-querying the database for the active event and injecting the `selectedEvento` entity into views. Also exposes the event switcher list (`allEventos`) to `admin` and `editor` roles.

The active-event database-driven pattern is one of the most important cross-cutting behaviors in the repository. Many index and add/edit actions use the session value to filter or prefill records. The session is kept in sync with the database `ativo` field on every request.

## 6. Domain Model

### 6.1 Main Business Entities

- `Evento`: an event or meeting context, with an `ativo` boolean field
- `Apoio`: a support text/document associated with an event and a GT
- `Item`: an agenda item belonging to a support text and optionally to a user (relator)
- `User`: an authenticated application user with a role
- `Votacao`: a recorded vote for a specific item in an event
- `Gt`: a working group (Grupo de Trabalho) that groups support texts

### 6.2 Relationship Graph

```text
Gt
  `- hasMany Apoios (foreignKey: gt_id, propertyName: apoios_list)

Evento
  |- hasMany Apoios
  `- hasMany Votacoes

Apoio
  |- belongsTo Evento
  |- belongsTo Gts (foreignKey: gt_id, propertyName: gt_entity)
  `- hasMany Items

Item
  |- belongsTo Apoio
  |- belongsTo User (optional owner/relator)
  `- hasMany Votacoes

Votacao
  |- belongsTo User
  |- belongsTo Evento
  `- belongsTo Item (propertyName: votacao_item)

User
  `- hasMany Votacoes
```

### 6.3 Event-Scoped Data Access

The selected event in session is used to restrict most data:

- `ApoiosController::index()` filters by `Apoios.evento_id`
- `ItemsController::index()` joins through `Apoios` to keep items inside the active event
- `VotacoesController::index()` filters by `Votacoes.evento_id`
- Add/edit flows often prefill `evento_id` using the selected event

This pattern makes the active event effectively a working context for most screens.

### 6.4 Group Scoping for Relators

Relator usernames follow the pattern `grupoN` (e.g. `grupo1`, `grupo15`). The group number is extracted via `substr($identity->username, 5)` and used to:

- Filter `Votacoes` by `grupo` in index, relatorio, and voting actions
- Restrict `Votacoes` visibility in `VotacaoPolicy::canView/canEdit/canDelete`
- Scope the `findItensSemVoto` custom finder
- Enforce group ownership in `votarItem`, `votarTr`, and `votarRestantes`

### 6.5 Inclusion Items (`.99` suffix)

Items whose code ends with `.99` are special "inclusion items" created during the voting workflow. They have specific visibility and access rules:

- Relators can only see `.99` items they own (via `user_id`)
- The `isInclusionItemCode()` method detects `.99` suffix
- `buildInclusionItemCode($tr)` generates the code `sprintf('%02d.99', $tr)`
- These items are excluded from TR status checks (`isTrSuprimida`, `isTrAprovada`) and from `findItensSemVoto`

## 7. Module Documentation

### 7.1 Application Infrastructure

#### `src/Application.php`

Responsibilities:

- Bootstraps framework-level application services
- Configures the middleware pipeline
- Defines authentication behavior
- Defines authorization service resolution

Key methods:

- `bootstrap()`: loads bootstrap files and configures ORM table locator behavior (fallback class disabled for non-CLI)
- `middleware()`: assembles the HTTP middleware queue
- `getAuthenticationService()`: enables session and form authentication with `DefaultUrlChecker`, redirecting unauthenticated users to `Users::login`
- `getAuthorizationService()`: enables ORM policy resolution via `OrmResolver`

Why it matters:

This file is the runtime root of the application. Authentication and authorization behavior starts here.

### 7.2 Events Module

Files:

- `src/Controller/EventosController.php`
- `src/Model/Table/EventosTable.php`
- `src/Model/Entity/Evento.php`
- `src/Policy/EventoPolicy.php`
- `templates/Eventos/`

Responsibilities:

- CRUD for events
- Displaying the list of events
- Activating an event (database source of truth)

Key controller actions:

- `index()`: paginated event list ordered by `ordem DESC`
- `view($id)`: event detail with nested `Apoios.Items`
- `add()`, `edit($id)`, `delete($id)`: standard CRUD
- `ativar($id)`: deactivates all other events, activates the selected one (admin/editor only)
- `select()`: legacy session-based event switch for admin/editor — also updates the database `ativo` field to stay in sync

Key table behavior:

- `EventosTable` defines `hasMany('Apoios')`
- `EventosTable` defines `hasMany('Votacoes')`
- Validates `ordem`, `nome`, `data`, and `local`
- The `ativo` boolean field exists in the schema but is not validated in `validationDefault()` (managed at the controller level)

Policy summary:

- Everyone can list and view events
- Only `admin` and `editor` can add, edit, delete, or activate events

### 7.3 GTs Module

Files:

- `src/Controller/GtsController.php`
- `src/Model/Table/GtsTable.php`
- `src/Model/Entity/Gt.php`
- `src/Policy/GtPolicy.php`
- `templates/Gts/`

Responsibilities:

- CRUD for working groups (Grupos de Trabalho)
- Serves as parent container for support texts via `gt_id`

Key controller actions:

- `index()`: paginated GT list (authorization skipped)
- `view($id)`: GT detail with related `Apoios`
- `add()`, `edit($id)`, `delete($id)`: standard CRUD with policy checks

Key table behavior:

- `GtsTable` defines `hasMany('Apoios')` via `gt_id` with `propertyName => 'apoios_list'`
- Validates `sigla` (required), `nome` (optional), `outras` (optional)
- Display field: `sigla`

Policy summary:

- Everyone can list and view GTs
- `admin` and `editor` can create and update
- Only `admin` can delete

### 7.4 Support Texts Module

Files:

- `src/Controller/ApoiosController.php`
- `src/Model/Table/ApoiosTable.php`
- `src/Model/Entity/Apoio.php`
- `src/Policy/ApoioPolicy.php`
- `templates/Apoios/`

Responsibilities:

- Managing support texts/documents linked to an event and a GT
- Serving as the parent container for agenda items
- Viewing support texts filtered by TR number

Key controller actions:

- `index()`: paginated support texts filtered to the active event, with optional text search on `autor` and `texto`. Eager-loads `Eventos` and `Gts`.
- `view($id)`: support text detail with related items (relator `.99` filtering applied)
- `viewtr()`: loads a support text by `evento_id` and `tr` query params, with related items filtered for relator visibility
- `add()`, `edit($id)`: assign the active event automatically; provide GT dropdown
- `delete($id)`: remove support text

Key table behavior:

- `belongsTo('Eventos')` (INNER join)
- `hasMany('Items')`
- `belongsTo('Gts')` via `gt_id` with `propertyName => 'gt_entity'` (LEFT join)
- Validates `nomedoevento`, `evento_id`, `caderno`, `numero_texto`, `tema`, `gt`, `gt_id`, `titulo`, `autor`, and `texto`

Policy summary:

- Everyone can list and view
- Only `admin` and `editor` can mutate records

### 7.5 Items Module

Files:

- `src/Controller/ItemsController.php`
- `src/Model/Table/ItemsTable.php`
- `src/Model/Entity/Item.php`
- `src/Policy/ItemPolicy.php`
- `templates/Items/`

Responsibilities:

- CRUD for agenda items under support texts
- Event-scoped item browsing
- TR filter with session persistence
- Relator-specific visibility and mutation rules for inclusion items ending in `.99`

Key controller actions:

- `index()`: loads items with `Apoios` and `Votacoes`, filtered by active event. TR filter persisted in session (`items_tr_filter`). Relator `.99` filtering applied. Available TR options extracted from item code prefixes.
- `view($id)`: item detail with votacoes (relator sees only their group's votes)
- `add()`: creates an item and assigns the current authenticated user as `user_id`
- `edit($id)`: prevents `user_id` tampering (stripped from request data)
- `delete($id)`: standard mutation with policy check

Role-specific behavior:

- Relators only see either:
  - items whose `item` code does not end with `.99`, or
  - `.99` items they own through `user_id`

Key table behavior:

- `belongsTo('Apoios')` (INNER join)
- `belongsTo('Users')` (optional, via `user_id`)
- `hasMany('Votacoes')`
- Validates `apoio_id`, `tr`, `item`, and `texto`

Policy summary:

- `admin`, `editor`, and `relator` can add items
- `admin` and `editor` can edit/delete any item
- `relator` can edit/delete only items they own (via `user_id`)
- `.99` items are not viewable by relators who don't own them

### 7.6 Users and Authentication Module

Files:

- `src/Controller/UsersController.php`
- `src/Model/Table/UsersTable.php`
- `src/Model/Entity/User.php`
- `src/Policy/UserPolicy.php`
- `templates/Users/`

Responsibilities:

- Login/logout flow
- User CRUD
- Password hashing
- Role storage for authorization decisions
- Admin impersonation feature

Key controller actions:

- `beforeFilter()`: allows unauthenticated access to `login`
- `login()`: checks authentication result and redirects on success
- `logout()`: clears the session, including `impersonated_by`
- `index()`: paginated user list (admin/editor only)
- `view($id)`: user detail with votacoes scoped to active event
- `add()`, `edit($id)`, `delete($id)`: user management
- `edit($id)`: prevents privilege escalation — non-admin/editor users cannot change `role`
- `impersonate($id)`: admin switches to another user's identity (stores original admin ID in `impersonated_by` session key)
- `stopImpersonate()`: restores the original admin identity

Key table behavior:

- `hasMany('Votacoes')`
- `Timestamp` behavior enabled
- Enforces unique `username`

Key entity behavior:

- `User::_setPassword()` hashes passwords via `DefaultPasswordHasher`
- `password` is hidden from JSON serialization

Policy summary:

- `admin` and `editor` can list and add users
- `admin`, `editor`, or the user themself can view/edit a profile
- Cannot delete self
- Only `admin` can impersonate another user (cannot impersonate self)

### 7.7 Voting Module

Files:

- `src/Controller/VotacoesController.php`
- `src/Model/Table/VotacoesTable.php`
- `src/Model/Entity/Votacao.php`
- `src/Policy/VotacaoPolicy.php`
- `templates/Votacoes/`

Responsibilities:

- Standard CRUD for votes
- Event-scoped vote listing with persistent TR and Grupo filters
- Multi-step TR voting workflow (4 phases)
- Inclusion item creation during voting
- Voting reports with markdown export
- Per-relator data restrictions

#### Standard CRUD Behavior

- `index()`: paginates votes with related `Users`, `Eventos`, and `Items`. TR and Grupo filters persisted via session (`votacoes_tr_filter`, `votacoes_grupo_filter`). Relators are automatically scoped to their own group.
- `view($id)`: shows vote detail
- `add()`: preloads active event and optional item; restricts relators from voting on `.99` items they don't own; handles inclusion result (`inclusão`) by creating/reusing a `.99` item
- `edit($id)`: similar checks to `add()`; also validates group ownership for relators
- `delete($id)`: deletes a vote after policy approval

#### Inclusion Item Workflow

When `resultado` is `inclusão` (or `inclusao`), the controller:

1. Looks up the `Apoio` for the given TR in the active event
2. Generates an inclusion item code (`sprintf('%02d.99', $tr)`)
3. Either reuses an existing linked `.99` item or creates a new one
4. Links the item to the vote and standardizes `resultado` to `inclusão`

Key private methods supporting this:

- `isInclusionResult($resultado)`: checks if result is `inclusão` or `inclusao`
- `applyInclusionItem(...)`: orchestrates item creation/reuse and vote linking
- `buildInclusionItemCode($tr)`: generates `XX.99` code
- `isInclusionItemCode($itemCode)`: detects `.99` suffix
- `ensureRelatorCanAccessItem(...)`: validates event ownership and relator `.99` access
- `buildItemOptions(...)`: builds dropdown with item ID and code
- `buildItemTextMap(...)`: maps item IDs to their text for JavaScript auto-fill
- `buildSelectableItemsQuery(...)`: base query with event and relator `.99` filtering
- `findExistingVoteForGroupItem(...)`: prevents duplicate votes
- `buildEventoOptions()`: builds event dropdown with ID and name

#### Multi-Step Voting Workflow

The project-specific workflow is implemented with four custom actions:

1. `votarTr($grupo, $tr)` — Phase 1: TR-level vote
   - Loads all items in a TR for the active event
   - Validates that the relator's group matches the `$grupo` parameter
   - If the TR is rejected (`suprimida`): creates one rejection vote per item using `saveMany()`, skipping items that already have votes for this group/event
   - If the TR is approved: no records are written, user proceeds to item voting

2. `votarItem($itemId)` — Phase 2: Individual item vote
   - Validates item belongs to active event
   - Checks for existing vote for this group/event/item combination; redirects to edit if found
   - Derives `grupo` from the relator's username (`substr(username, 5)`)
   - Stamps `user_id`, `evento_id`, `grupo`, `tr`, `item`, and `data`
   - Supports `destaque_minoria` flag

3. `votarRestantes($grupo, $tr)` — Phase 3: Bulk approval
   - Uses `findItensSemVoto` custom finder to get items without votes
   - Bulk-creates `aprovada` votes for all remaining items
   - Excludes `.99` items from the finder

4. `inserirItem($grupo, $tr)` — Phase 4: Insert new item during voting
   - Creates a new `Item` with code `XX.99` under the TR's `Apoio`
   - Creates a `Votacao` with `resultado = 'inclusão'` for the new item
   - Rolls back the created item if the vote save fails

#### Reporting

- `relatorio()`: accepts a comma-separated list of TR numbers via `?trs=` query param
- Loads matching votacoes in the active event with `Users` and `Items` contained
- Relators are automatically filtered to their own group's votes
- Supports markdown export via `?download=markdown` query param, producing a formatted report with event header, per-TR sections, item text, vote tables, modification proposals, and inclusion text

#### Key Table Logic

`VotacoesTable` contains the most important model-specific logic:

- `belongsTo('Users')` (INNER)
- `belongsTo('Eventos')` (INNER)
- `belongsTo('Items')` (INNER, `propertyName => 'votacao_item'`)
- `validationDefault()` validates vote fields, including:
  - required `grupo`, `tr`, `item`, `resultado`, `votacao`
  - `votacao` must match `XX/XX/XX` style input such as `15/6/0`
  - `item_modificada` is required when `resultado` is `modificada`
- `findItensSemVoto()`: custom finder that returns TR items not yet voted by a specific group in an event. Takes `grupo`, `tr`, `evento_id`, and `user_id` options. Excludes `.99` items.
- `isTrSuprimida($tr, $eventoId)`: returns `true` when all non-`.99` items in a TR have been voted as `suprimida`
- `isTrAprovada($tr, $eventoId)`: returns `true` when all non-`.99` items in a TR have been voted as `aprovada` with no modification

#### Resultado Values

The `resultado` field uses these values:

- `aprovada` — item approved without modification
- `modificada` — item approved with modification (requires `item_modificada` text)
- `suprimida` — item suppressed/rejected
- `inclusão` — new item inserted during voting (standardized to the accented form)

#### Entity Notes

`Votacao` stores:

- user, event, group, TR, and item references
- voting result and tally string
- modification text (`item_modificada`)
- free-text observations
- timestamp
- minority-highlight flag `destaque_minoria`
- The associated `Item` is accessible via `$votacao->votacao_item` (not `$votacao->item`)

#### Policy Summary

- Everyone can list votes and view reports
- `admin` and `relator` can add votes
- `admin`, `editor`, or the relator who owns the vote (group match) can edit/delete
- Only `relator` can execute `votarTr`, `votarItem`, `votarRestantes`, and `inserirItem` actions
- Relator vote visibility is restricted to their own group

## 8. Authorization Model

Authorization uses CakePHP's policy system from `src/Policy/`.

### 8.1 Roles in Use

- `admin`
- `editor`
- `relator`

### 8.2 Effective Permission Matrix

| Action | admin | editor | relator |
|---|---|---|---|
| List/View events, apoios, items, GTs | yes | yes | yes |
| Add/Edit/Delete events | yes | yes | no |
| Activate event | yes | yes | no |
| Add/Edit/Delete apoios | yes | yes | no |
| Add items | yes | yes | yes |
| Edit/Delete items | yes | yes | own only |
| View `.99` items | yes | yes | own only |
| List users | yes | yes | no |
| View/Edit user profile | yes | yes | self |
| Add/Delete users | yes | yes | no |
| Impersonate user | yes | no | no |
| Add votes | yes | no | yes |
| Edit/Delete votes | yes | yes | own group |
| votarTr / votarItem / votarRestantes / inserirItem | no | no | yes |
| View reports | yes | yes | yes |

### 8.3 Important Detail

Some controller actions call `skipAuthorization()` even though policy methods exist for them. In practice, this means the effective access model is partly enforced by explicit controller logic (identity role checks, group validation, `.99` ownership checks) and partly by policies. The controller implementation should be treated as the source of truth for runtime behavior.

## 9. Key Classes and Functions

### 9.1 Most Central Classes

- `App\Application`
- `App\Controller\AppController`
- `App\Controller\VotacoesController`
- `App\Model\Table\VotacoesTable`
- `App\Model\Table\ItemsTable`
- `App\Model\Table\ApoiosTable`
- `App\Policy\VotacaoPolicy`
- `App\Policy\ItemPolicy`
- `App\Policy\GtPolicy`
- `App\Model\Entity\User`

### 9.2 Important Methods

- `Application::middleware()`
- `Application::getAuthenticationService()`
- `AppController::beforeFilter()` — database `ativo` event synchronization
- `AppController::beforeRender()` — active event injection into views
- `EventosController::ativar()` — database-driven event activation
- `EventosController::select()` — legacy session + DB sync event switch
- `VotacoesController::votarTr()`
- `VotacoesController::votarItem()`
- `VotacoesController::votarRestantes()`
- `VotacoesController::inserirItem()`
- `VotacoesController::relatorio()`
- `VotacoesController::applyInclusionItem()`
- `VotacoesController::ensureRelatorCanAccessItem()`
- `VotacoesTable::findItensSemVoto()`
- `VotacoesTable::isTrSuprimida()`
- `VotacoesTable::isTrAprovada()`
- `User::_setPassword()`
- `UsersController::impersonate()` / `stopImpersonate()`

## 10. Dependency Relationships

### 10.1 Layer Dependencies

```text
Routes
  -> Controllers
  -> Policies

Controllers
  -> ORM Tables
  -> Authentication / Authorization components
  -> Session state
  -> Templates

Tables
  -> Entities
  -> Validation rules
  -> Association graph

Policies
  -> Authenticated identity
  -> Domain entities
```

### 10.2 Module Coupling

- Events are the top-level business context
- GTs are parent containers for Apoios
- Apoios depend on Eventos and Gts
- Items depend on Apoios and may depend on Users for ownership
- Votacoes depend on Items, Users, and Eventos simultaneously
- Reporting depends on the full item -> vote -> user chain

### 10.3 Cross-Cutting Dependencies

- Database `ativo` field: the source of truth for the active event, synchronized to session `selected_evento_id` on every request
- Session filter state: `votacoes_tr_filter`, `votacoes_grupo_filter`, `items_tr_filter`
- Authentication identity: role checks and relator ownership/group checks
- Username pattern `grupoN`: group number extraction via `substr(username, 5)`
- ORM containment and joins: most listing screens depend on eager loading and join filtering
- `propertyName` overrides: `votacao_item` (VotacoesTable.Items), `gt_entity` (ApoiosTable.Gts), `apoios_list` (GtsTable.Apoios)

## 11. Database and Migrations

### 11.1 Schema Sources

- Test schema: `tests/schema.sql`
- Application migrations: `config/Migrations/`

### 11.2 Migrations

| Migration | Description |
|---|---|
| `20260622045047_AddDestaqueMinoriaToVotacoes` | Adds boolean `destaque_minoria` to `votacoes` |
| `20260622120000_CreateGts` | Creates the `gts` table |
| `20260627003337_RemoveTrSuprimidaTrAprovadaFromVotacoes` | Removes `tr_suprimida` and `tr_aprovada` columns from `votacoes` (status now computed at runtime) |
| `20260627021710_AddAtivoToEventos` | Adds boolean `ativo` to `eventos` for database-driven active event selection |
| `20260701230626_AddUserIdToItems` | Adds `user_id` to `items` for relator ownership of inclusion items |

### 11.3 Database Tables

- `eventos` — includes `ativo` boolean
- `apoios` — includes `gt_id` foreign key and `gt` text field
- `items` — includes `user_id` for ownership
- `users`
- `votacoes` — includes `item_modificada`, `destaque_minoria`
- `gts` — `sigla`, `nome`, `outras`

## 12. Views and Presentation

The application is rendered through PHP templates under `templates/`.

Notable view areas:

- `templates/Eventos/`: event CRUD pages
- `templates/Gts/`: GT CRUD pages
- `templates/Apoios/`: support text pages, including `viewtr.php` for TR-specific viewing
- `templates/Items/`: item pages with TR filter
- `templates/Users/`: user pages, login form, and impersonation controls
- `templates/Votacoes/`: vote CRUD, report, workflow screens (`votar_tr`, `votar_item`, `votar_restantes`, `inserir_item`)

The UI is conventional CakePHP server-rendered HTML. There is no separate frontend application or asset build pipeline. JavaScript is used for form field auto-fill and conditional visibility (e.g. `item_modificada` visibility based on `resultado` selection).

## 13. Testing and Quality Tooling

### 13.1 Test Structure

- `tests/TestCase/Controller/`
- `tests/TestCase/Model/Table/`
- `tests/TestCase/Policy/`
- `tests/TestCase/Command/`
- `tests/bootstrap.php`

### 13.2 Test Bootstrap Behavior

`tests/bootstrap.php`:

- loads the main bootstrap
- configures SQLite for `debug_kit` in tests
- fixes the current time with `Chronos::setTestNow()`
- loads schema from `tests/schema.sql`

### 13.3 Quality Commands

- `composer run test`
- `composer run cs-check`
- `composer run cs-fix`
- `composer run stan`
- `composer run check`

## 14. How To Run The Project

### 14.1 Install Dependencies

```bash
composer install
```

During install, the post-install hook in `src/Console/Installer.php` can:

- create `config/app_local.php`
- create writable directories
- generate a security salt

### 14.2 Configure Environment

Review and adjust:

- `config/app_local.php`
- `config/app.php`

If needed, you can also use a `.env` file based on `config/.env.example`, but the loader is commented out by default in `config/bootstrap.php`.

### 14.3 Run The Development Server

```bash
bin/cake server -p 8765
```

Then open `http://localhost:8765`.

### 14.4 Run Tests

```bash
composer run test
```

Or:

```bash
vendor/bin/phpunit
```

### 14.5 Run Static Checks

```bash
composer run cs-check
composer run stan
composer run check
```

## 15. Practical Reading Guide

For a new maintainer, the fastest way to understand the repository is:

1. Read `src/Application.php`
2. Read `src/Controller/AppController.php` — especially the `ativo` field synchronization
3. Read `src/Controller/VotacoesController.php` — the densest business logic
4. Read `src/Model/Table/VotacoesTable.php` — custom finders and TR status methods
5. Read `src/Controller/ItemsController.php` and `src/Policy/ItemPolicy.php`
6. Read `src/Controller/EventosController.php` — event activation logic
7. Read `src/Controller/ApoiosController.php` — GT association and `viewtr` action
8. Review `tests/` to confirm expected behavior

## 16. Maintenance Notes

- The repository still contains the generic CakePHP skeleton `README.md`; the actual business behavior is defined in the application source files, not the README.
- Fallback routing is still enabled, which is convenient for development but broadens the URL surface.
- The database `ativo` field is the source of truth for the active event. The `AppController` synchronizes it to the session on every request. Changes to this pattern will affect most modules.
- The voting module contains the densest business logic and should be treated as the primary risk area for future changes.
- Inclusion items (`.99` suffix) are created dynamically during the voting workflow and have special visibility, access, and filtering rules across multiple layers.
- The `propertyName` overrides on three associations (`votacao_item`, `gt_entity`, `apoios_list`) exist to avoid naming collisions with scalar fields of the same name.
- TR status (`suprimida`/`aprovada`) is computed at runtime by `VotacoesTable::isTrSuprimida()` and `isTrAprovada()`, not stored as columns (removed by migration `20260627003337`).
- The `relatorio()` method supports markdown export via `?download=markdown`.
- Admin impersonation stores the original admin ID in `impersonated_by` session key and is cleared on logout.
