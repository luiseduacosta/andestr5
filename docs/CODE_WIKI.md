# Code Wiki

## 1. Project Summary

This repository is a CakePHP 5 application for managing events, support texts, agenda items, users, and voting records. The application is server-rendered and follows CakePHP's standard MVC organization:

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
|- src/
|  |- Controller/        # MVC controllers
|  |- Model/
|  |  |- Entity/         # ORM entities
|  |  `- Table/          # ORM tables and validation
|  |- Policy/            # Authorization policies
|  |- View/              # View classes
|  `- Application.php    # App bootstrap and middleware
|- templates/            # Server-rendered views
|- tests/                # PHPUnit tests, fixtures, schema bootstrap
|- webroot/              # Public document root
|- composer.json         # PHP dependencies and scripts
`- README.md             # Generic CakePHP skeleton instructions
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
- Session-scoped "active event" context shared across screens
- CRUD controllers plus a custom multi-step voting workflow

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
- Ensuring a session value named `selected_evento_id` exists
- Defaulting the active event to the newest event when none is selected
- Injecting the selected event into views
- Exposing the event switcher list to `admin` and `editor` roles

The active event session context is one of the most important cross-cutting behaviors in the repository. Many index and add/edit actions use it to filter or prefill records.

## 6. Domain Model

### 6.1 Main Business Entities

- `Evento`: an event or meeting context
- `Apoio`: a support text/document associated with an event
- `Item`: an item belonging to a support text and optionally to a relator
- `User`: an authenticated application user with a role
- `Votacao`: a recorded vote for a specific item in an event

### 6.2 Relationship Graph

```text
Evento
  |- hasMany Apoios
  `- hasMany Votacoes

Apoio
  |- belongsTo Evento
  `- hasMany Items

Item
  |- belongsTo Apoio
  |- belongsTo User (optional owner/relator)
  `- hasMany Votacoes

Votacao
  |- belongsTo User
  |- belongsTo Evento
  `- belongsTo Item

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

## 7. Module Documentation

### 7.1 Application Infrastructure

#### `src/Application.php`

Responsibilities:

- Bootstraps framework-level application services
- Configures the middleware pipeline
- Defines authentication behavior
- Defines authorization service resolution

Key methods:

- `bootstrap()`: loads bootstrap files and configures ORM table locator behavior
- `middleware()`: assembles the HTTP middleware queue
- `getAuthenticationService()`: enables session and form authentication, redirecting unauthenticated users to `Users::login`
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
- Persisting the currently active event in the user session

Key controller actions:

- `index()`: paginated event list
- `view($id)`: event detail with apoios, items, and votes
- `add()`, `edit($id)`, `delete($id)`: standard CRUD
- `select()`: writes `selected_evento_id` into session for `admin`/`editor`

Key table behavior:

- `EventosTable` defines `hasMany('Apoios')`
- `EventosTable` defines `hasMany('Votacoes')`
- Validates `ordem`, `nome`, `data`, and `local`

Policy summary:

- Everyone can list and view events
- Only `admin` and `editor` can add, edit, or delete

### 7.3 Support Texts Module

Files:

- `src/Controller/ApoiosController.php`
- `src/Model/Table/ApoiosTable.php`
- `src/Model/Entity/Apoio.php`
- `src/Policy/ApoioPolicy.php`
- `templates/Apoios/`

Responsibilities:

- Managing support texts/documents linked to an event
- Serving as the parent container for agenda items

Key controller actions:

- `index()`: paginated support texts filtered to the active event
- `view($id)`: support text detail with related items
- `add()`, `edit($id)`: assign the active event automatically
- `delete($id)`: remove support text

Key table behavior:

- `belongsTo('Eventos')`
- `hasMany('Items')`
- Validates metadata fields like `caderno`, `numero_texto`, `tema`, `titulo`, `autor`, and large `texto`

Policy summary:

- Everyone can list and view
- Only `admin` and `editor` can mutate records

### 7.4 Items Module

Files:

- `src/Controller/ItemsController.php`
- `src/Model/Table/ItemsTable.php`
- `src/Model/Entity/Item.php`
- `src/Policy/ItemPolicy.php`
- `templates/Items/`

Responsibilities:

- CRUD for agenda items under support texts
- Event-scoped item browsing
- Relator-specific visibility and mutation rules for special items ending in `99`

Key controller actions:

- `index()`: loads items with `Apoios` and `Votacoes`, filtered by active event
- `view($id)`: item detail
- `add()`: creates an item and assigns the current authenticated user as `user_id`
- `edit($id)`, `delete($id)`: standard mutation operations with policy checks

Role-specific behavior:

- Relators only see either:
  - items whose `item` code does not end with `99`, or
  - `99` items they own through `user_id`

Key table behavior:

- `belongsTo('Apoios')`
- `belongsTo('Users')`
- `hasMany('Votacoes')`
- Validates `apoio_id`, `tr`, `item`, and `texto`

Policy summary:

- `admin`, `editor`, and `relator` can add/edit/delete in general
- For relators, `*99` items are only viewable/editable/deletable by their owner

### 7.5 Users and Authentication Module

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

Key controller actions:

- `beforeFilter()`: allows unauthenticated access to `login`
- `login()`: checks authentication result and redirects on success
- `logout()`: clears the session
- `index()`, `view($id)`, `add()`, `edit($id)`, `delete($id)`: user management

Key table behavior:

- `hasMany('Votacoes')`
- `Timestamp` behavior enabled
- Enforces unique `username`

Key entity behavior:

- `User::_setPassword()` hashes passwords via `DefaultPasswordHasher`
- `password` is hidden from JSON serialization

Policy summary:

- Everyone can list and view users
- Only `admin` and `editor` can add/delete users
- `admin`, `editor`, or the user themself can edit a profile

### 7.6 Voting Module

Files:

- `src/Controller/VotacoesController.php`
- `src/Model/Table/VotacoesTable.php`
- `src/Model/Entity/Votacao.php`
- `src/Policy/VotacaoPolicy.php`
- `templates/Votacoes/`

Responsibilities:

- Standard CRUD for votes
- Event-scoped vote listing
- Multi-step TR voting workflow
- Voting reports
- Per-relator data restrictions

#### Standard CRUD Behavior

- `index()`: paginates votes with related `Users`, `Eventos`, and `Items`
- `view($id)`: shows vote detail
- `add()`: preloads active event and optional item; restricts relators from voting on another relator's `*99` items
- `edit($id)`: similar checks to `add()`
- `delete($id)`: deletes a vote after policy approval

#### Multi-Step Voting Workflow

The project-specific workflow is implemented with three custom actions:

1. `votarTr($grupo, $tr)`
   - Loads all items in a TR for the active event
   - If the TR is rejected, creates one rejection vote per item using `saveMany()`
   - If the TR is approved, no records are written at this stage

2. `votarItem($itemId)`
   - Records an individual vote for one item in discussion
   - Pulls `grupo` from the related `Apoio` record (`gt_id`)
   - Stamps `user_id`, `evento_id`, `tr`, `item`, and `data`

3. `votarRestantes($grupo, $tr)`
   - Finds items in the TR without votes in the active event
   - Bulk-creates approval votes for all remaining items

These three methods implement the business workflow more than any other part of the repository.

#### Reporting

- `report()`: accepts a comma-separated list of TR numbers
- Loads matching items in the active event
- Eager-loads votes and voting users
- Extracts votes flagged with `destaque_minoria`

#### Key Table Logic

`VotacoesTable` contains the most important model-specific logic:

- `belongsTo('Users')`
- `belongsTo('Eventos')`
- `belongsTo('Items')`
- `validationDefault()` validates vote fields, including:
  - required `grupo`, `tr`, `item`, `resultado`, `votacao`
  - `votacao` must match `XX/XX/XX` style input such as `15/6/0`
- `findItensSemVoto()` is a custom finder that returns TR items not yet voted in the active event

#### Entity Notes

`Votacao` stores:

- user, event, group, TR, and item references
- voting result and tally string
- free-text observations
- timestamp
- minority-highlight flag `destaque_minoria`

#### Policy Summary

- Everyone can list votes and view reports
- `admin` and `relator` can add votes and run voting actions
- `admin` can edit/delete any vote
- A `relator` can edit/delete only their own vote records

## 8. Authorization Model

Authorization uses CakePHP's policy system from `src/Policy/`.

### 8.1 Roles in Use

- `admin`
- `editor`
- `relator`

### 8.2 Effective Permission Shape

- `admin`: full access across all modules
- `editor`: manages events, support texts, and users, and can change active event
- `relator`: can create/manage items, vote, and is restricted on protected `*99` items they do not own

### 8.3 Important Detail

Some controller actions call `skipAuthorization()` even though policy methods exist for them. In practice, this means the effective access model is partly enforced by explicit controller logic and partly by policies. The wiki should treat the controller implementation as the source of truth for runtime behavior.

## 9. Key Classes and Functions

### 9.1 Most Central Classes

- `App\Application`
- `App\Controller\AppController`
- `App\Controller\VotacoesController`
- `App\Model\Table\VotacoesTable`
- `App\Model\Table\ItemsTable`
- `App\Policy\VotacaoPolicy`
- `App\Policy\ItemPolicy`
- `App\Model\Entity\User`

### 9.2 Important Methods

- `Application::middleware()`
- `Application::getAuthenticationService()`
- `AppController::beforeFilter()`
- `AppController::beforeRender()`
- `EventosController::select()`
- `VotacoesController::votarTr()`
- `VotacoesController::votarItem()`
- `VotacoesController::votarRestantes()`
- `VotacoesController::report()`
- `VotacoesTable::findItensSemVoto()`
- `User::_setPassword()`

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
- Apoios depend on Eventos
- Items depend on Apoios and may depend on Users for ownership
- Votacoes depend on Items, Users, and Eventos simultaneously
- Reporting depends on the full item -> vote -> user chain

### 10.3 Cross-Cutting Dependencies

- Session state: `selected_evento_id`
- Authentication identity: role checks and relator ownership checks
- ORM containment and joins: most listing screens depend on eager loading and join filtering

## 11. Database and Migrations

### 11.1 Schema Sources

- Test schema: `tests/schema.sql`
- Application migrations: `config/Migrations/`

### 11.2 Notable Migration

`config/Migrations/20260622045047_AddDestaqueMinoriaToVotacoes.php` adds the boolean field `destaque_minoria` to `votacoes`, which is later surfaced by the report screen.

### 11.3 Important Tables

- `eventos`
- `apoios`
- `items`
- `users`
- `votacoes`

There is also a `gts` table in the test schema, but the currently scanned application layer centers on the five tables above.

## 12. Views and Presentation

The application is rendered through PHP templates under `templates/`.

Notable view areas:

- `templates/Eventos/`: event CRUD pages
- `templates/Apoios/`: support text pages
- `templates/Items/`: item pages
- `templates/Users/`: user pages and login form
- `templates/Votacoes/`: vote CRUD, report, and workflow screens

The UI is conventional CakePHP server-rendered HTML. There is no separate frontend application or asset build pipeline.

## 13. Testing and Quality Tooling

### 13.1 Test Structure

- `tests/TestCase/Controller/`
- `tests/TestCase/Model/Table/`
- `tests/TestCase/Policy/`
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
2. Read `src/Controller/AppController.php`
3. Read `src/Controller/VotacoesController.php`
4. Read `src/Model/Table/VotacoesTable.php`
5. Read `src/Controller/ItemsController.php` and `src/Policy/ItemPolicy.php`
6. Read `src/Controller/EventosController.php`
7. Review `tests/` to confirm expected behavior

## 16. Maintenance Notes

- The repository still contains the generic CakePHP skeleton `README.md`; the actual business behavior is defined in the application source files, not the README.
- Fallback routing is still enabled, which is convenient for development but broadens the URL surface.
- The active-event session pattern is foundational. Changes to it will affect most modules.
- The voting module contains the densest business logic and should be treated as the primary risk area for future changes.
