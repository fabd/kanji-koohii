# CLAUDE.md

## About the Project

Kanji Koohii is a web app for Japanese language learners to study and review kanji (Japanese characters). The two main features are **Study pages** (browse ~3000 kanji with mnemonics and shared community stories) and **Flashcard Review** (SRS using the Leitner algorithm).

## Commands

`dcweb` is an alias for `docker exec web bash`. The `web` docker container is setup with Apache, PHP and nodeJS. Use `docker compose up -d` to start the dev environment.

npm should be run in the container, eg. `dcweb -c "cd vite; npm install (package)"`.

```bash
dcweb -c "cd vite; npm run dev"       # Vite dev server (HMR)
dcweb -c "cd vite; npm run build"     # production build
dcweb -c "cd vite; vue-tsc --noEmit"  # type-check Typescript code (including .vue files)
./vendor/bin/php-cs-fixer -q fix (filename.php)    # format PHP and fix coding standards issues
./vendor/bin/phpstan analyse (filename.php)        # check PHP code with PHPStan
```

## Tech Stack & Project Structure

- Docker: PHP/Apache container (Ubuntu 24.04, PHP 8.3) + MariaDB container
- Vite dev server
- Backend uses Symfony 1.4 and composer packages
- Frontend uses Vite + Vue 3 + TypeScript + Tailwind CSS 4

Docker container runs PHP 8.3; we write PHP 8.2-compatible code to match the production server.

The folder structure is a typical Symfony 1.4 project with some additional folders.

The `vite` folder is the root folder for the front-end, it is organized in a vertical structure where JS, TS and CSS are grouped by features or the pages they are used on.

```
# (paths relative to src/)
apps/
  koohii/                    Symfony app folder
    config/                  Symfony app config files (app.yml, routing.yml, etc)
    modules/                 MVC controllers (study, review, manage, account, api, …)
      (name)/
        actions/
          actions.class.php  standard Symfony actions
        templates/
          (name)Success.php  standard Symfony template for an action
    lib/                     app-specific PHP classes
    templates/
      layout.php             the global HTML template
batch/                       build and admin scripts — do not run these unless explicitly instructed
  build-prod.sh
config/                      generated files, do not edit files in this folder
data/
  schemas/
    revtk_schema.sql         the database schema
docs/                        see Documentation section below
lib/                         classes that are not app-specific
  core/                      classes that extend Symfony
    coreDatabase.php         custom ORM base
    coreDatabaseTable.php    base class for peer classes
    coreWebResponse.php      extends sfWebResponse to resolve CSS & JS assets served by Vite
  peer/                      peer classes to model the database
    KanjisPeer.php           peer model for the kanjis table in the database
    *Peer.php                one peer class for each table in the database
    ...
test/                        we don't use tests, folder is not relevant to most tasks
tools/                       folder is not relevant to most tasks
vite/                        this is the root folder for Vite dev server and Vite build
  src/                       all the frontend code lives here (typescript, css, and Vue files)
    app/                     app-specific modules and page logic
      account/               account & settings pages
      admin/                 admin dashboard related
      api/                   async API based on an Axios wrapper that always resolves
                             (errors are returned as values, never thrown).
      common/                features that are not page-specific go here
        components/          components that are not page-specific
        main.build.css       the main stylesheet
        root-bundle.ts       utilities available to all pages (imported by entry-common.ts)
      css/                   common styles, most stylesheets here are included in main.build.css
        legacy/              do not add new code here — only move code out of this folder
      dict/                  related to the kanji/japanese dictionary features
      home/                  home page dashboard
      landing/               landing page
      learn-more/            the Learn More page
      lessons/               View All Lessons page, components related to lessons
      main/                  the SRS chart page
      manage/                the Manage Flashcards pages
      nav/                   mobile and desktop nav
      recognition/           Kanji Recognition (WIP)
      review/                Flashcard Review functionality
      review-custom/         Custom Review page
      study/                 the Study page, study search box, shared stories, all study related
      support/               the Support/Donate page
      ux/                    the /ux section of the app (internal docs & demos, admin only)
    lib/                     helpers and utilities that are not page-specific
    types/                   TypeScript type definitions
    entry-common.ts          loaded on every page, *in addition* to the page specific entry
    entry-home.ts            dashboard
    entry-study.ts           study pages
    entry-review.ts          SRS chart + flashcard review
    entry-recognition.ts     kanji recognition/writing practice
    entry-manage.ts          manage flashcards
    entry-account.ts         account settings
    entry-landing.ts         signed-out landing page    
    entry-styleguide.ts      private docs & testing ground (admin only)
web/                         the root folder served by Apache
  index.php                  the default backend entry point
```

## Frontend Architecture

This is **not a SPA**:

- most pages are rendered by PHP, and enhanced with Javascript
- does not use routing
- does not use state management
- components can communicate with an event bus (`vite/src/lib/EventBus.ts`)

Here is a table matching entries and what pages they are served on:

| Entry point            | Used on                             |
| ---------------------- | ----------------------------------- |
| `entry-account.ts`     | /account                            |
| `entry-common.ts`      | (Every page)                        |
| `entry-home.ts`        | / (homepage, when signed in)        |
| `entry-landing.ts`     | / (landing page, when signed out)   |
| `entry-manage.ts`      | /manage                             |
| `entry-recognition.ts` | /misc/reading (WIP feature)         |
| `entry-review.ts`      | /main (SRS chart) /review/*         |
| `entry-study.ts`       | /study/*                            |
| `entry-styleguide.ts`  | /ux (accessible only to admin user) |

## Coding Standards

- Use 2-space indentation for PHP, TypeScript, and CSS

### CSS

- prefix CSS classes with `ko-` (eg. `ko-Dialog` for a `Dialog` component)
- use the pattern `ko-(component name)-(descendant)--(modifier)` (eg. `ko-Dialog-title`)
- BEM modifier `--` for component variants, eg. `ko-Dialog--small`
- use `is-` prefix for runtime state (eg. `is-active`)
- **Tailwind**: prefer using Tailwind for layout utilities while keeping component-specific styling (colors, borders, backgrounds) in custom CSS files

### PHP

- PHP 8.2 syntax with constructor property promotion
- PSR-1, PSR-2, PSR-4, PSR-12 standards
- Strict comparisons only (===, !==)
- Braces required for all control structures

When you add/modify PHP code, run PHPStan after to check for errors.

When you have finished a task involving PHP code, run php-cs-fixer once to properly format the code.

**Common Symfony patterns:**

```php
// Get current user ID in an action
$userId = kk_get_user()->getUserId();

// Get database handle
$db = kk_get_database();

// Query via peer class
KanjisPeer::getInstance()->select('ucs_id')->where('kanji = ?', $s)->query();
```

### PHPDoc

- Only add PHPDoc when it adds information not already expressed by type hints

### TypeScript

- use kebab-case for filenames (eg. root-bundle.ts)
- use PascalCase for classes (eg. KoDialog)
- use alias `@` in imports in place of `src` (eg. `src/app/file` => `@/app/file`)


When you have finished a task involving TypeScript code, run `dcweb -c "cd vite; vue-tsc --noEmit"` to check for errors.

### Vue

- use the Options API
- use `Ko` prefix for Vue components (eg. KoKanjiCard.vue)

## Domain Terminology

Used throughout code, comments, and variable names:

| Term         | Meaning                                               |
| ------------ | ----------------------------------------------------- |
| RTK          | *Remembering the Kanji* — book series by James Heisig |
| frame number | also known as "RTK index" (e.g. 一 = 1, 二 = 2)       |
| UCS / ucsId  | Unicode code point identifying a kanji (UCS-2)        |
| userId       | Integer value uniquely identifying a user             |

## Documentation

Read the following docs if it is relevant to your task, before you start working:

- **docs/core-database-orm.md** : read when writing queries or working with peer classes in `lib/peer/`
- **docs/front-end-api.md** : read when adding or modifying frontend API calls or backend API endpoints
- **docs/symfony-mvc.md** : read when creating a new module, action, template, or routing on the backend
- **docs/vite-entries.md** : read when creating/modifying Vite entries, or passing data from PHP to frontend via KK globals
- **docs/front-end-dom.md** : read when writing DOM manipulation, event handling, or mounting Vue components from non-Vue code

## External References

- Symfony 1.2 definitive guide (applies to 1.4): https://symfony.com/legacy/doc/book/1_2
- Symfony 1.4 reference: https://symfony.com/legacy/doc/reference
