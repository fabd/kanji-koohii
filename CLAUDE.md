# CLAUDE.md

## About the Project

Kanji Koohii is a web app for Japanese language learners to study and review kanji (Japanese characters). The two main features are **Study pages** (browse ~3000 kanji with mnemonics and shared community stories) and **Flashcard Review** (SRS using the Leitner algorithm).

## Commands

`dcweb` is an alias for `docker exec web bash`.

Running commands with `dcweb` defaults to `/var/www/html` in the container, which maps to the `src/` folder on the host.

All npm scripts must be run from `src/vite/` (where `package.json` lives).

```bash
npm run dev          # Vite dev server (HMR)
npm run build        # Type-check + production build
npm run buildfast    # Production build (skip type-check)
npm run lint         # Lint .js/.ts/.vue files with ESLint
npm run type-check   # Type check Typescript code (including .vue files)

dcweb -c "./vendor/bin/phpstan file.php"   # use PHPStan (from the Docker container)
```

Production build calls `src/batch/build-prod.sh`, which runs Vite then parses the manifest into `src/config/vite-build.inc.php` for PHP template injection.

Tests: a `src/test/` folder exists but tests are not currently used.

## Architecture

### Backend — Symfony 1.4 (PHP 8.3)

- Entry point: `src/web/index.php` (prod) / `index_dev.php` (dev)
- Symfony app: `src/apps/koohii/`
  - `modules/` — MVC controllers (study, review, manage, account, api, …)
  - `lib/` — app-specific PHP classes
  - `templates/layout.php` — global HTML template
- Shared libraries: `src/lib/`
  - `core/coreDatabase.php` — custom ORM base
  - `core/coreDatabaseTable.php` — base class for peer classes
  - `peer/` — one peer class per DB table (e.g. `KanjisPeer`, `UsersPeer`)
- Database schema: `src/data/schemas/revtk_schema.sql`

**Common Symfony patterns:**

```php
// Get current user ID in an action
$userId = kk_get_user()->getUserId();

// Get database handle
$db = kk_get_database();

// Query via peer class
KanjisPeer::getInstance()->select('ucs_id')->where('kanji = ?', $s)->query();
```

API endpoints live in `src/apps/koohii/modules/api/`.

### Frontend — Vite 8 + Vue 3 + TypeScript + Tailwind CSS 4

Located in `src/vite/src/`. This is **not a SPA** — Vite produces multiple per-page bundles:

| Entry point        | Used on                      |
| ------------------ | ---------------------------- |
| `entry-common.ts`  | Every page                   |
| `entry-home.ts`    | Dashboard                    |
| `entry-study.ts`   | Study pages                  |
| `entry-review.ts`  | SRS chart + flashcard review |
| `entry-manage.ts`  | Manage flashcards            |
| `entry-account.ts` | Account settings             |
| `entry-landing.ts` | Signed-out landing page      |

Each entry uses `domContentLoaded()` to defer initialization until DOM + deferred scripts are ready.

Frontend structure:

- `src/app/` — core logic (API client, review flow, study page)
- `src/vue/` — Vue 3 SFC components (Koohii-prefixed; Element Plus also used)
- `src/lib/` — utilities
- `src/types/` — TypeScript type definitions
- `src/assets/css/` — stylesheets; `main.build.css` is the main stylesheet

PHP loads the correct bundle via `coreWebResponse::addViteEntries()` using the generated manifest at `src/config/vite-build.inc.php`. An module/action can specify the bundle to use in the module's `config/view.yml` file by setting the `javascripts` key. If the javascript filename starts with `src/` then coreWebResponse treats it as a bundle, and includes the required javascript and css dependencies.

### Infrastructure

- Docker: PHP/Apache container (Ubuntu 24.04, PHP 8.3) + MariaDB container
- Vite dev server

## Coding Standards

- **2 spaces** for indentation (PHP and TypeScript)
- **Strict equality** always: `===` / `!==`
- **Interface names** prefixed with `I` (e.g. `IUserService`)
- All new functions and classes need **JSDoc** (TypeScript) or **PHPDoc** (PHP) comments
- Use PHP features up to 8.3

When naming CSS classes, use the following conventions:

- prefix with `ko-`, eg. `ko-Dialog` for a `Dialog` component
- prefix modifier and state classes with `is-`, eg. `is-active`
- use the pattern `ko-(component name)-(descendant)--(modifier)` for example `ko-Dialog-title` `ko-Dialog--small`

## Domain Terminology

Used throughout code, comments, and variable names:

| Term         | Meaning                                               |
| ------------ | ----------------------------------------------------- |
| RTK          | *Remembering the Kanji* — book series by James Heisig |
| frame number | RTK index for a kanji (e.g. 一 = 1, 二 = 2)           |
| UCS / ucsId  | Unicode code point identifying a kanji (UCS-2)        |
| userId       | Integer value uniquely identifying a user             |

## Documentation

Read the following docs if it is relevant to your task, before you start working:

| Document                          | Contents                       |
| --------------------------------- | ------------------------------ |
| src/data/schemas/revtk_schema.sql | The database schema            |
| agent_docs/core-database-orm.md   | Reference for the database ORM |

## External References

- Symfony 1.2 definitive guide (applies to 1.4): https://symfony.com/legacy/doc/book/1_2
- Symfony 1.4 reference: https://symfony.com/legacy/doc/reference
