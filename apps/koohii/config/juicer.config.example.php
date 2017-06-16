<?php
/**
 * Configuration file used by Juicer (a build tool for css/js).
 *
 * 
 * Setup
 *
 *   Copy to `juicer.config.php` (same folder)
 *   
 *   Edit the paths to point to local repositories
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
 *   Juicer handles basic dependencies like that through a simple include syntax. See
 *   for example :
 * 
 *     web/revtk/bundles/flashcardreview-1.0.juicy.js
 *
 *
 *   Asset Packaging
 *   
 *   Juicer also has a "provide" directive similar to SprocketS. This informs Juicer
 *   that there are assets that can be deployed with the js / css file. When Juicer
 *   finds a provide directive, it looks for files (typically images) in subfolders
 *   and copies them automatically to an output folder.
 *
 *   The configuration files declares mappings between source folders and what the
 *   output structure should look like. This help ensure that provided assets don't
 *   collide.
 *
 * 
 *   Variables
 *
 *   Juicer also has a very crude variable replacement. Notice a few css colors are
 *   declared below and are referenced %LIKE_THIS% in the `.juicy.css` files.
 *
 *
 *   How is Juicer used?
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
 *   YUI2      Yahoo's YUI2 is the current javascript library in use in RevTK.
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
 * Installation
 * 
 * - Download YUI2 "Full Developer Kit" from http://developer.yahoo.com/yui/2/
 *   and set the YUI2  paths accordingly (point them to the /build folder).
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

  // old
  'YUI2'     => '/home/fab/Development/Frameworks/yui_2.9.0/build',

  'FRONT'    => './lib/front',

  // vendor libraries
  'VENDOR'   => './web/vendor',

  // assets from Core  widgets will go to web/corejs/
  'CORE'     => './lib/front/corejs',

  // assets from RevTK widgets will go to web/build/revtk
  'REVTK'    => './lib/front/revtk',

  // Include source for legacy front end code still living in the web/ folder
  'WEB'      => './web',

  //
  // Stylesheet color constants
  //
  'CSS_BG_BODY'   =>    '#f4f0e5',

  // shaded background 1
  'CSS_BG_1'      =>    '#e7e1d3',

  // top nav background color
  'CSS_BG_NAV'    =>    '#ede8de',

  // subdued background 1 (lighter)
  'CSS_BG_1M'     =>    '#ede8de',

  // subdued background 1 : border
  'CSS_BG_1S'     =>    '#d4cdba',

  // green backgrounds & text
  'CSS_BG_GL'     =>    '#D7EBB4',
  'CSS_BG_GD'     =>    '#475636',
 

  // widgets: table row (slightly brighter than CSS_BG_1)
  'CSS_TBL_BG'    =>    '#f8f6ef',

  // text colors for due/undue cards
  'CSS_SRS_DUE'   =>    '#f7a247',
  'CSS_SRS_UNDUE' =>    '#72c569',


  'MAPPINGS' => array
  (
    // CoreJs dependencies
    './lib/front/corejs'  => 'build/corejs',

    // RevTK front end assets go to the build/ folder
    './lib/front/revtk'   => 'build/revtk',

    // Mapping YUI asset dependencies to the Web folder
    '/home/fab/Development/Frameworks/yui_2.9.0/build'     => 'build/yui2'
  )
);
