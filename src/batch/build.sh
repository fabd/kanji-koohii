#!/bin/bash
#
# Build frontend files for test & production environments.
#
#
#    PACKAGING
#
#    Juicer is used to package assets, and concatenate css/js from many files (typically
#    organized into smaller reusable units aka "components"). While doing so, Juicer creates
#    a folder structure in web/build/
#
#    Juicer maps all the referenced assets in the stylesheets (ie. images), to the /web/build/
#    folder, and copies all the provided assets there. This is signaled to Juicer with special
#    directives such as =require and =provide (which are declared in normal css comments).
#
#    MINIFYING
#
#    Once files are "juiced" (*.juiced.css/js) they are compressed. Currently yuicompressor is
#    used for css, closure is used for js. This could be changed to other tools (TODO).
#
#    VERSIONING
#
#    Once css/js files are packaged and minified into the web/build/ folder, we want to scan
#    them and use the timestamp for versioning files to the clients. The timestamps are used
#    to create unique filenames; to ensure client browsers refresh their cache.
#
#    batch/build_app.php scans the web/ folder for css/js files and generates a config file
#    with filenames and timestamps in config/versioning.inc.php
#
#    This file is used by coreWebResponse.php (extending Symfony's sfWebResponse) to output
#    versioned css/js urls.
#
#    Finally, the .htaccess file has a rewrite rule which maps versioned assets to the
#    build files.
#
#    DEVELOPMENT VS TEST/PROD
#
#    In development, versioning is disabled. coreWebResponse generates asset urls that point
#    directly to the *.juicy.(css|js) files, via a php script. This php script requests files
#    through Juicer, and pipes the result back to the browser. Thus providing a form of
#    "hot reload" for stylesheets.
#
#
#  USAGE
#
#    Production build (run from the root folder)!
#
#    $ batch/build.sh  --all
#
#
#  SEE
#
#    /lib/core/coreWebResponse.php    ( => getVersionUrl() )
#    /lib/juicer/Juicer.php
#    /batch/build_app.php
#    /web/version/cache.php
#
#
#  SETUP
#
#    $ npm install jshint --save-dev
#    $ npm install postcss --save-dev
#
#
#  TODO
#
#  - production urls generated in coreWebResponse is inefficient, pre-generate them in
#    the versioning.inc.php ?
#

# node modules
JSHINT='./node_modules/.bin/jshint'
POSTCSS='./node_modules/.bin/postcss'
UGLIFYJS='./node_modules/.bin/uglifyjs'

# replace web/ with web/build/ for production css/js
PATH_WEB=web/
PATH_WEB_BUILD=web/build/

# Files to build    *.juicy.js > *.juiced.js > *.min.js
javascripts=(
  'web/revtk/labs-alpha-flashcardreview'
  'web/revtk/manage'
  'web/revtk/study-base'
  'web/revtk/kanji-flashcardreview'
  'web/revtk/bundles/yui-base'
)

# Files to build    *.juicy.css > *.juiced.css > *.min.css
stylesheets=(
  'web/koohii/home'
  'web/revtk/main'
  'web/revtk/manage'
  'web/revtk/study-base'
  'web/revtk/kanji-flashcardreview'
  'web/revtk/review-home'
)


# Juicer strips some debug code (not "console.log" which is checked for further below)
JUICEROPTS='-v --strip ''Core.log,Core.warn,Core.halt,Core.assert'' --webroot web --config apps/koohii/config/juicer.config.php'


# colored output  eg.  echo "${red}red text ${green}green text${reset}"
TEXT_BOLD=`tput bold`
TEXT_FAIL=`tput bold ; tput setaf 1`
TEXT_RESET=`tput sgr0`
TEXT_GREEN=`tput setaf 2`

failMessage() {
  error_msg=$1
  printf "\n${TEXT_FAIL}   FAIL   ${error_msg}\n\n${TEXT_RESET}"
  exit
}
successMessage() {
  printf "\n${TEXT_BOLD}${TEXT_GREEN}$1${TEXT_RESET}"
}

function show_help()
{
  echo ''
  echo 'Lint & minify all CSS and JS (note: legacy build! Vue is built with Webpack)'
  echo ''
  echo 'Build options:'
  echo '  --all       Production build (lint,css,js)'
  echo ''
  echo '  --css       Build only stylesheets'
  echo '  --js        Build only javascripts'
  echo ''
  echo '  --version   Update config/versioning.inc.php'
  echo ''
  exit
}

function do_lint_js_files()
{
  # Run JsLint on all javascript files, using nodejs (http://nodejs.org/) and jslint-utils by Mike West.

  LINT_FILES=`find web/revtk -name '*.js'`
  LINT_FILES="$LINT_FILES `find lib/front/corejs -name '*.js'`"
  LINT_FILES="$LINT_FILES `find lib/front/revtk -name '*.js'`"

  # JsHint config file in json format, see http://jshint.com/docs/
  JSHINT_OPTS=batch/tools/jshint/jshint.conf.json

  #for FOO in `git diff HEAD --name-only | grep '.js$'`; do

  printf "\n${TEXT_BOLD} Linting javascript files...${TEXT_RESET}\n"

  for FILE_TO_LINT in $LINT_FILES ; do

    # Ignore temporary files that I prefix with "__"
    if [[ $FILE_TO_LINT =~ '__' ]] ; then continue ; fi

    # Ignore pre-packed files from third party libraries
    if [[ $FILE_TO_LINT == *.pack.js ]] ; then continue; fi

    # Skip file with BOM mark that chokes jslint (NOTE: fonctionne avec jshint!)
    #if [[ $FILE_TO_LINT =~ web/revtk/study/keywords-rt[hk].js ]] ; then continue ; fi

    printf "\n   $FILE_TO_LINT ... "

    $JSHINT --config=$JSHINT_OPTS "${FILE_TO_LINT}"

    # Break if file does not lint
    RETVAL=$?
    if (( $RETVAL )) ; then
      failMessage "JSHint failed on:  $FILE_TO_LINT"
      exit 1
    fi

    printf "${TEXT_BOLD}${TEXT_GREEN}OK${TEXT_RESET}"

  done

  printf "\n\n"
  successMessage "   Linting complete.\n\n"
}

function do_build_css()
{
  for file in ${stylesheets[*]}; do
    # Split
    # f=(`echo $files | tr "," "\n"`)

    P_JUICY=${file}.juicy.css
    P_JUICED=${file/$PATH_WEB/$PATH_WEB_BUILD}.juiced.css
    P_MINIFIED=${file/$PATH_WEB/$PATH_WEB_BUILD}.min.css

    # Juice
    php lib/juicer/JuicerCLI.php $JUICEROPTS -i $P_JUICY -o $P_JUICED
    if (( $? )) ; then
      failMessage "Juicer failed."
    fi

    # TODO: lint css files (jslint chokes on YUI's minified styles)

    # Minify
    printf "\n${TEXT_BOLD} Minifying  ${TEXT_RESET}${P_MINIFIED}  ...\n\n"
    
    $POSTCSS $P_JUICED -o $P_MINIFIED
    if (( $? )) ; then
      failMessage "postcss failed."
    fi

  done

  successMessage "   CSS build complete.\n\n"
}

function do_build_js()
{
  do_check_for_console_log

  for file in ${javascripts[*]}; do
    # Split
    # f=(`echo $files | tr "," "\n"`)

    P_JUICY=${file}.juicy.js
    P_JUICED=${file/$PATH_WEB/$PATH_WEB_BUILD}.juiced.js
    P_MINIFIED=${file/$PATH_WEB/$PATH_WEB_BUILD}.min.js

    # Juice
    php lib/juicer/JuicerCLI.php $JUICEROPTS -i $P_JUICY -o $P_JUICED
    if (( $? )) ; then
      failMessage "Juicer failed."
    fi

    printf "\n${TEXT_BOLD} Minifying  ${TEXT_RESET}${P_MINIFIED}  ...\n\n"
    $UGLIFYJS $P_JUICED -o $P_MINIFIED --compress

    if (( $? )) ; then
      failMessage "Closure Compiler minification failed."
    fi

  done

  successMessage "   JS build complete.\n\n"
}

function do_check_for_console_log()
{
  #  --ignore-dir
  #
  #    lib/front/vue    Ignore because console.* calls are removed by UglifyJsPlugin (drop_console) in the Webpack build.
  #
  #
    # Detect uncommented console.log() calls and stops if any is found.
    # Even if those can be stripped with Juicer, it's likely there is debugging code left behind.
  ack-grep --ignore-dir web/vendor --ignore-dir web/build/pack --ignore-dir lib/front/vue -g -i '(?<!.juiced)(?<!.min)\.js' | ack-grep -x '^\s*console\.log\('
  if [ $? -eq 0 ] ; then failMessage "Uncommented console.log() call." ; fi

  #ack-grep -g -i 'lib/front/vue/.*(\.vue|\.js)'| ack-grep -x '^\s*Core\.log\('
  #if [ $? -eq 0 ] ; then failMessage "Uncommented Core.log() call." ; fi
  #exit
}

function do_build_versioning()
{
  # update version file for revisioning css & js assets
  P_VERSION_OLD=config/.versioning.inc.php
  P_VERSION_NEW=config/versioning.inc.php
  printf "\n${TEXT_BOLD} Rebuild  $P_VERSION_NEW ... ${TEXT_RESET}\n\n"
  mv $P_VERSION_NEW $P_VERSION_OLD
  php batch/build_app.php -w web -o $P_VERSION_NEW
}

if [ $# -eq 0 ]; then
  show_help
fi



if [ $1 = '--lint' ]; then

  do_lint_js_files

elif [ "$1" = '--css' ]; then

  do_build_css

elif [ "$1" = '--js' ]; then

  do_build_js

elif [ "$1" = '--version' ]; then

  do_build_versioning

elif [ "$1" = '--all' ]; then
  # --all

  do_lint_js_files
  do_build_css
  do_build_js

  do_build_versioning

  successMessage " PRODUCTION build complete.\n\n"

else

  show_help

fi
