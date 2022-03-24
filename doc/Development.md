<h1>Development Notes</h1>

1. [VSCode Setup](#vscode-setup)
2. [Vite Usage Notes](#vite-usage-notes)
   1. [USE_DEV_SERVER true / false](#use_dev_server-true--false)
   2. [Static asset handling](#static-asset-handling)
   3. [Versioning of CSS/JS not handled by Vite](#versioning-of-cssjs-not-handled-by-vite)
3. [Scripts execution](#scripts-execution)
4. [Refactoring Roadmap](#refactoring-roadmap)
      1. [Remove all YUI2 dependencies](#remove-all-yui2-dependencies)

# VSCode Setup

- **Volar**
  - disable builtin TS/JS Language Support (disable in workspace only)
    https://github.com/johnsoncodehk/volar/discussions/471#discussioncomment-1361669


# Vite Usage Notes

Vite support in Kanji Koohii repo is implemented via the overloading of symfony's `sfWebResponse` class. See `coreWebResponse.php` as well as `src/lib/Vite.php`.

Vite entry files (as configured in vite.config.ts) can be created for any of the php pages. The corresponding symfony module/action simply needs to include the entry file as is, for example `src/entry-study.ts`. This is usually configured in the module's `view.yml` file.

In **development mode** coreWebResponse.php will add the Vite client to the page. The Vite client will parse the raw entry file and handle all dependencies using the browser's native ESM support. This can add some latency to the page.

In **test/production modes** coreWebResponse.php will use `manifest.json` (from the last `vite build`), and add the entry's relevant CSS/JS dependencies to the page automatically.

By default the repo is configured to work in Vite's "dev server" mode, which requires the Vite dev server running in order to view the site properly.

_From the php/apache container:_

```bash
$ cd vite
$ vite
```

**Vite's HMR** works out of the box, and the page should refresh automatically after editing any of the dependencies of the Vite entry file(s) used on the current page.

**Tailwind JIT** is available in all js/ts/vue and php files! Note: Tailwind JIT doesn't watch php files when using `vite build --watch`. Either restart `vite build`, or figure something with `nodemon`. This isn't really an issue as most tailwind use will be inside Vue components.

## USE_DEV_SERVER true / false

In coreWebResponse.php, setting `USE_DEV_SERVER = false` allows to skip the Vite client, and avoids any added latency especially noticable with the stylesheets.

Using `USE_DEV_SERVER = false`, php will parse Vite's `manifest.json` on each page load, and include the relevant CSS/JS dependencies just as it would in production mode.

This works well with `vite build --watch`, but keep in mind Tailwind JIT won't refresh in this mode.

## Static asset handling

Context : https://vitejs.dev/guide/assets.html#static-asset-handling

For now, make sure to use absolute paths (relative to the `./web` folder), in Vue templates and stylesheets.

Eg. `/assets/icons/foo.svg` would match `./web/assets/icons/foo.svg`

This is because the php/apache serves from the ./web/ folder. Otherwise we would need some kind of symbolic link solution as Vite's dev server doesn't match the php/apache web path -- and it also changes between dev/prod builds. Using /abs/paths is much simpler.

If an image changes, simply rename it eg. `logo-20210815.gif`. For now we'd rather avoid using Vite (Rollup)'s handling of static assets with the filename hashes etc. as it causes a lot more noise in the manifest.json as well as CLI output and for the most part we don't really need this functionality.

**Very small files** could be included in the src/vite/ folder, taking advantage of [build.assetsInlineLimit](https://vitejs.dev/config/#build-assetsinlinelimit) option. However last time I checked, it still is an issue because in development it wasn't URL-encoding, therefore not matching the www server path.

## Versioning of CSS/JS not handled by Vite

It is possible to add versioning to JS/CSS that is not handled by Vite.

There is a mod_rewrite rule in `.htaccess` which matches a filename pattern containing a version number (eg. a YYYYMMDD date), and routes these through a php scrip that sets far future expire headers.

    RewriteRule ^(.*)\.[a-z0-9]+\.(css|js)$   /version/cache.php?file=$0 [L]

A file named...

    /web/vendor/some-lib.min.js

... is referenced in the app (php/js) as:

    /web/vendor/some-lib-v20210815.min.js

The `htaccess` rule will match the pattern, and get the original file, add far future expire headers, and return it as gzipped content.

# Scripts execution

1. **Vite bundles** are included in the document `<head>`<br>
   ... because they are ESM modules, they are [implicitly defered](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/script#attr-defer)

2. **php outputs data for Vue templating via a global `window.KK` object**
   <br>... because `<script>` tags are part of the document, this data is parsed _before_ defered modules are executed.

3. **defered ESM modules are executed**
   <br>... after the document has been parsed, but before firing `DOMContentLoaded`

4. **DOMContentLoaded events firing**
   <br>(a) any `DOMContentLoaded` events set up via `<script>` tag in php template will execute first since they were parsed as part of the document
   <br>(b) followed by `DOMContentLoaded` events set up in ESM modules, in the order they were included in the page

# Refactoring Roadmap

### Remove all YUI2 dependencies

`yui2-bundle.min.js` contains code mainly used in `AjaxDialog` (draggable dialogs
as seen on flashcard review page, "edit flashcard", "edit story" dialogs):

`./web/build/yui2/yui2-bundle.min.js` is 165 kb !

    yahoo-dom-event.js (38 kb !)
    /animation/animation-min.js (14 kb)
    /connection/connection-min.js (13 kb)
    /container/container-min.js (77 kb !!)
    /dragdrop/dragdrop-min.js (24 kb !)

Note! `yahoo-dom-event.js` can only be removed last, as it's a dependency for YUI2's Container, DragDrop etc.

| Status   | What                                                                                                                                                                     |
| -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **DONE** | remove all references to YUI2 **Dom**, **Event** in own bundles                                                                                                          |
| ...      | remove YUI2 **Connect** (`connection-min.js` 13.3 kb) : refactor AjaxRequest to use Axios?                                                                               |
| ...      | remove YUI2 **Animation** : I'm not sure it's even used anymore, it was at some point used to add a slight fade in/fade out to the dialogs (same as Vue's "transitions") |
| ...      | remove YUI2 **Container**, **DragDrop** : replace all dialogs with a new Vue-based dialog, maybe element-plus ?                                                          |
