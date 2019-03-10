<?php
/**
 * Configuration file used by Juicer (a build tool for css/js).
 *
 *   
 * What is Juicer?
 * 
 *   Juicer is a build tool for css/js files with both CLI use (build script)
 *   and "hot reload" via Apache mod_rewrite.
 *
 *   It was inspired by https://github.com/rails/sprockets and has similar
 *   syntax.
 *
 *   In a nutshell, Juicer allows to split up js and css into small, maintainable files,
 *   then create “meta-files” that include the smaller files in a logical order.
 *
 *   Juicer handles basic dependencies like that through a custom include syntax.
 *   See for example:
 *   
 *     web/revtk/main.juicy.css
 *
 *
 *   Asset Packaging
 *   
 *   Juicer also has a "provide" directive similar to Sprockets. This informs Juicer
 *   that there are assets that can be deployed with the js / css file. When Juicer
 *   finds a provide directive, it looks for files (typically images) in subfolders
 *   and copies them automatically to an output folder.
 *
 *   The configuration files declares mappings between source folders and what the
 *   output structure should look like. This help ensure that provided assets don't
 *   collide.
 *
 *
 *   Development Vs Production modes
 *
 *   1. In development, a "hot reload" feature is provided with a simple mod_rewrite
 *      trick. See .htaccess and web/version/cache.php. A .juicy.css|js request
 *      simply goes through Juicer and the output of Juicer goes to the browser.
 *
 *   2. For production, `batch/build` script loops through a bunch of ".juicy.*"
 *      files. In production, the mod_rewrite trick is not used because minified
 *      assets are referenced instead. You can see the mechanism, along with
 *      the versioning system in  lib/core/coreWebResponse.php
 *
 *
 * Command line usage:
 * 
 *   If a css/js file doesn't load in the browser try running Juicer on the command line
 *   to debug what is happening. For example:
 *
 *   $ php lib/juicer/JuicerCLI.php -v --webroot web --config apps/koohii/config/juicer.config.php --infile web/revtk/main.juicy.css
 * 
 *
 * Explanations
 * 
 *   YUI2      Yahoo's YUI2 (legacy code, to be phased out)
 *
 *   FRONT     This is meant to be the equivalent of the /lib folder, but for
 *             front end code. Any files in here can only be sourced through
 *             Juicer, since they are not in the server's public web/ folder!
 *
 *   WEB       This one points to the public web folder. For legacy code, it is
 *             easier to access files from there. Any image dependencies from
 *             YUI2 or FRONT will also be copied here.
 *
 *   MAPPINGS  This tells Juicer for asset dependencies such as images, where
 *             to copy them in the web folder. This keeps things neatly arranged
 *             and also in a predictable location.
 * 
 * 
 * See
 *
 *   lib/juicer/Juicer.php
 *   lib/juicer/JuicerCLI.php
 * 
 */

return array
(
  // ABSOLUTE paths must start with "/"
  // RELATIVE paths must start with "./" and are relative to SF_ROOT_DIR (Symfony root path)

  'FRONT'    => './lib/front',

  // vendor libraries
  'VENDOR'   => './web/vendor',

  // legacy dependency, to be phased out
  'YUI2'     => './web/vendor/yui2-build',

  // assets from Core  widgets will go to web/corejs/
  'CORE'     => './lib/front/corejs',

  // assets from RevTK widgets will go to web/build/revtk
  'REVTK'    => './lib/front/revtk',

  // Include source for legacy front end code still living in the web/ folder
  'WEB'      => './web',


  'MAPPINGS' => array
  (
    // CoreJs dependencies
    './lib/front/corejs'  => 'build/corejs',

    // RevTK front end assets go to the build/ folder
    './lib/front/revtk'   => 'build/revtk',

    // Mapping YUI asset dependencies to the Web folder
    './web/vendor/yui2-build' => 'build/yui2'
  )
);
