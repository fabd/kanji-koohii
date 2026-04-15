# Symfony MVC — Wiring a New Module

## Overview

A Symfony 1.4 **module** is a self-contained MVC unit: one actions class, one templates folder, and optional config files. This document covers every step to add a new module from scratch in this project.

## Directory Structure

```
apps/koohii/
  config/
    routing.yml          app-level routes
    security.yml         app-level security defaults
  modules/
    (module)/
      actions/
        actions.class.php    action methods (one per page/endpoint)
      config/
        security.yml         per-module security overrides  (optional)
        view.yml             page titles, Vite bundles       (optional)
      templates/
        (action)Success.php  rendered when an action returns sfView::SUCCESS
        _(partial).php       partials (underscore prefix)
```

## Step-by-Step

### 1. Create the module folder structure

```
apps/koohii/modules/(module)/
  actions/
    actions.class.php
  config/
    security.yml   (if needed)
    view.yml       (if needed)
  templates/
    indexSuccess.php
```

### 2. Create the actions class

The class must be named `(module)Actions` and extend `sfActions`.

```php
<?php
// apps/koohii/modules/tools/actions/actions.class.php

class toolsActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    // set template variables as $this->varName — available in the template as $varName
    $this->title = 'Tools';

    // returning nothing (or sfView::SUCCESS) renders (action)Success.php
  }

  public function executeDetail(sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('id'));
    $this->itemId = (int) $request->getParameter('id');
  }
}
```

The return value of an action controls which template is rendered:

| Return value      | Template rendered           |
| ----------------- | --------------------------- |
| _(nothing)_       | `(action)Success.php`       |
| `sfView::SUCCESS` | `(action)Success.php`       |
| `sfView::ERROR`   | `(action)Error.php`         |
| `sfView::NONE`    | No template (used for JSON) |
| `$this->renderJson($data)` | JSON response (no template) |
| `$this->forward('module', 'action')` | Delegate to another action |
| `$this->redirect('url')` | HTTP redirect |

### 3. Create a template

Templates are plain PHP files. Variables set in the action via `$this->varName` are available as `$varName`.

```php
<?php
// apps/koohii/modules/tools/templates/indexSuccess.php
?>
<div class="row">
  <div class="col-lg-12">
    <h1><?php echo $title ?></h1>
  </div>
</div>
```

**Partials** (reusable template fragments) use an underscore prefix and are included via `include_partial()`:

```php
// In a template or another partial:
<?php include_partial('tools/SomeWidget', ['param' => $value]); ?>

// Partial file: apps/koohii/modules/tools/templates/_SomeWidget.php
// Variables passed in the array are available directly as $param, etc.
```

### 4. Add a route

Add a named route to `apps/koohii/config/routing.yml`. Named routes take precedence over the generic catch-all rules at the bottom of the file.

```yaml
# apps/koohii/config/routing.yml

tools:
  url:   /tools
  param: { module: tools, action: index }

tools_detail:
  url:   /tools/:id
  param: { module: tools, action: detail, id: '' }
  requirements: { id: '\d+' }
```

The generic catch-all rules at the bottom of `routing.yml` (`/:module/:action/*`) also work without an explicit route entry — useful during development.

### 5. Configure security

By default the app-level `security.yml` requires authentication (`is_secure: true`) for all modules.

**To require login for the whole module** (this is already the default — no file needed):

```yaml
# apps/koohii/modules/tools/config/security.yml
all:
  is_secure: true
```

**To allow public access to all actions in the module:**

```yaml
all:
  is_secure: false
```

**To mix public and secured actions:**

```yaml
all:
  is_secure: true

index:
  is_secure: false
```

### 6. Configure the view (page title and Vite bundle)

`view.yml` sets per-action metadata and associates a Vite entry point (JS/CSS bundle).

```yaml
# apps/koohii/modules/tools/config/view.yml

all:
  metas:
    title: "Tools - Kanji Koohii"
  javascripts:
    - src/entry-tools.ts

indexSuccess:
  metas:
    title: "Tools Overview - Kanji Koohii"
```

- `all:` applies to every action in the module; per-action keys override it.
- The `javascripts` key accepts a Vite entry path (`src/entry-*.ts`). `coreWebResponse` resolves the associated JS and CSS chunks from the Vite manifest.
- Omit `view.yml` if no Vite bundle is needed and the default title is acceptable.

## Adding an API Endpoint to an Existing Module

For JSON-only endpoints (no rendered template) the action returns `$this->renderJson($tron)`. No template file is needed. See [front-end-api.md](front-end-api.md) for the full pattern including the TypeScript side.

```php
public function executeMyaction(sfWebRequest $request)
{
  // JSON body is sent by the front-end API
  $params = $request->getContentJson();
  $ucsId  = (int) $params->ucsId;

  $tron = new JsTron();

  // ... business logic ...

  $tron->add(['result' => 'ok']);

  return $this->renderJson($tron);
}
```

The URL `/tools/myaction` maps to `toolsActions::executeMyaction()` via the generic routing rule — no route entry needed for API-only actions.

## Common Helpers

```php
// Get the authenticated user's ID
$userId = kk_get_user()->getUserId();

// Get a database handle
$db = kk_get_database();

// Read a POST/GET parameter
$value = $request->getParameter('key', 'default');

// Read a JSON request body (sent by the front-end API)
$params = $request->getContentJson();

// Abort with 404
$this->forward404Unless($condition);

// Pass data to the global JS context (read via `getGlobals()` on the front end)
kk_globals_put('MY_DATA', $data);
```
