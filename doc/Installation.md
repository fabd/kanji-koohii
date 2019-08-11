# Table of Contents
1. [Installation](#install)
2. [About the project](#about)
3. [Working with the Php-Apache container
](#webserver)
4. [Working with the MySQL container](#database)
5. [Troubleshooting](#troubleshooting)
6. [Useful Aliases](#tips)
7. [F.A.Q.](#faq)


# Installation <a name="install"></a>

## Requirements

Install [Docker CE](https://docs.docker.com/install/).

##  First Time Setup

Setup files for docker volumes that persist bash history in the containers:

    touch docker/db/bash/.bash_history docker/php-apache/bash/.bash_history

Build the containers:

    docker-compose up -d

*Note right after the containers are up, MySQL may take a minute to setup the local database (check with `docker-compose logs -f`). A new folder `mysql56/` will be created in the root directory, which maintains the state of the database.*

Start bash from the `web` container:

    docker-compose exec web bash

You should see a colored prompt: `[php] root /var/www/html $`.

*Note: the path `/var/www/html` corresponds to the Symfony root folder found in `src/`.*

From the `web` container, init some directories:

    mkdir -p cache log ; chmod 777 cache log
    mkdir -p web/build ; chmod 777 web web/build

Also init some private config files:

    cp web/.htaccess_default web/.htaccess
    cp apps/koohii/config/app.example.yml apps/koohii/config/app.yml
    cp apps/koohii/config/settings.example.yml apps/koohii/config/settings.yml

Init node packages (ignore warnings about "fsevents" and "ajv"):

    npm install

Run the webpack build (you can also use `npm run watch`):

    npm run dev

You should see something like this (files are output to `src/web/build/pack/`):

                    Asset     Size          Chunks             Chunk Names
    landing-bundle.raw.js  678 KiB  landing-bundle  [emitted]  landing-bundle
    review-bundle.raw.js   580 KiB   review-bundle  [emitted]  review-bundle
    root-bundle.raw.js     731 KiB     root-bundle  [emitted]  root-bundle
    study-bundle.raw.js    497 KiB    study-bundle  [emitted]  study-bundle



**Done!**

You should be able to see the homepage: http://localhost/index_dev.php

Sign in with user `guest` pw `test` (or create an account, no emails are sent in development mode).


# About the project <a name="about"></a>

Please note Kanji Koohii first came online in the summer of 2005! The code base is still largely based on Symfony 1.4 (with some tweaks here and there).

**Docker Setup**: we use two simple containers: `web` for Php 7.0 & Apache, `db` for MysQL 5.6. For convenience, both containers maintain bash history and custom aliases through a Docker volume (see `docker-compose.yml`).

**The Webpack build**: newer developments can benefit from Webpack 4, babel, Vue, and modern Javascript.

- Currently Vue components are located in `src/lib/front/vue/`.
- Use `npm run dev` or `npm run watch`, refresh the page after making a change
- Legacy stylesheets were converted to SCSS file extension, and included in the Webpack bundles (root/study/review/etc).
- Legacy stylesheets are located in `web/koohii/*.build.scss`

**The legacy build**: long before the upgrade to Webpack 4, the scripts & stylesheets were built with a custom tool called Juicer. The legacy Javascript bundles were based on YUI2 framework. As it's very time consuming to refactor, there are still old scripts in use.

- The legacy scripts are in `web/revtk/` folder, with a `*.juicy.js` naming pattern.
- "Juicy" JS files have a "hot reload" through a mod_rewrite rule in the .htaccess file.
- Make sure to 'Disable cache' in your Chrome Console.
- The `src/batch/build.sh` script is only required for test/staging/prod.


## The Symfony 1 Project Structure

Some bootstrap code is in `apps/koohii/config/koohiiConfiguration.class.php`.

All application configuration is in `apps/koohii/config/` Yaml files: `app.yml`, `settings.yml`, `routing.yml`, etc.

The global layout is in `apps/koohii/templates/layout.php`.


# Working with the Php-Apache container <a name="webserver"></a>

## Development & test builds

Development build is the default environment accessed via `index.php` or `index_dev.php`:


## Webpack

See `package.json`.

## Using virtual host name

You can use a virtual host name instead of `localhost`, for example (`/etc/hosts`):

    127.0.0.1    koohii.local

To access with: http://koohii.local

Make sure it matches the `ServerName` in `.docker/php-apache/koohii.vhosts.conf`, then rebuild the `web` container (`dc down ; dc build ; dc up -d`).

Also make sure to update `website_url` setting located in `src/apps/koohii/config/app.yml`. This setting is used to generate links in a few places.


# Working with the MySQL container <a name="database"></a>

The local database is initialized from a SQL file located in `.docker/db/initdb.d/`. This folder is mapped to the MySQL Docker image (cf.  [Initializing a fresh instance](https://hub.docker.com/_/mysql/#initializing-a-fresh-instance)).

The database state itself is maintained through a volume. The first time you run the *db*  service, a `mysql56` folder will appear in the root directory on your host. If you delete this folder, any changes like new user accounts, stories and flashcards will be lost.

### About the sample database

See [Database.md](./Database.md) in doc/  folder.

### MySQL Workbench

You can use [MySQL Workbench](https://dev.mysql.com/downloads/workbench/) on your host to run queries through a GUI. Use `localhost` and port `3306`.

### Using MySQL from the command line

To use mysql CLI, start bash from the `db` container:

    dc exec db bash
    [mysql] root /etc/mysql $ 

Then run **mysql** CLI:

    mysql -u koohii -pkoohii -h localhost -D db_github --default-character-set=utf8

You can also add an alias in `.docker/db/root/.bashrc`.


### Rebuild / reset the sample database

Source files are in `docker/db/initdb.d/`.

Should you need to reset the database:

    $ sudo rm -rf mysql56

Then rebuild the MySQL container:

    $ dc down ; dc build ; dc up -d


# Useful Aliases <a name="tips"></a>

## Alias to search the codebase with **grep**

    grepkk() {
      grep $@ --color=auto -inr \
              --include=*.{css,html,ini,js,md,php,sql,vue,yml} \
              --exclude=*{.min.js,.juiced.js,.min.css,.juiced.css} \
              --exclude-dir={node_modules,web/build} . ;
    }



## Alias to search the codebase with **ack**

Install ack-grep: `sudo apt-get install ack-grep`

    ackk()  { 
      ack -i \
          --type-add koohii:ext:css,html,ini,js,md,php,sql,vue,yml --koohii \
          --ignore-dir=web/build \
          --ignore-dir=web/vendor \
          --ignore-dir=lib/vendor/symfony \
          --ignore-file=match:/juicy.css$/ \
          --ignore-file=match:/juicy.js$/ \
          --ignore-file=match:/juiced.css$/ \
          --ignore-file=match:/juiced.js$/ \
          --ignore-file=match:/min.css$/ \
          --ignore-file=match:/min.js$/ \
          $@
    }


## Alias to get the **UCS code** from a CJK character

    # Convert to UCS (-C enable unicode features)
    ucs() { echo $@ | perl -C -pe 'print "UCS Code: ".ord($_)."\nCheck:    ".pack("U", ord($_))."\n"'; }

Example:

    $ ucs 上
    UCS Code: 19978


## **Sublime Text** project configuration

Select *Project > Edit Project* and add after "path":

    "file_exclude_patterns": [
        "src/cache",
        "src/node_modules",
        "src/web/build",
        "*.bak",
        "*.dat",
        "*.juiced.css",
        "*.juiced.js",
        "*.min.css",
        "*.min.js",
        "*.mwb",
        "*.utf8"
    ]

# F.A.Q.  <a name="faq"></a>

## Docker

Suggested aliases:

    alias dc='docker-compose'
    alias dk='docker'

Make sure both containers are running, and don't show "exit 0":

    $ dc ps

    Name                 Command             State              Ports       
    --------------------------------------------------------------------------------
    kk_mysql       docker-entrypoint.sh mysqld   Up      0.0.0.0:3306->3306/tcp     
    kk_webserver   apache2ctl -D FOREGROUND      Up      443/tcp, 0.0.0.0:80->80/tcp


If one container exited (eg. Apache complaining that httpd is already running), try:

    dc stop && dc up -d

You can also check the logs:

    dc logs -f


## Developing with Symfony 1.x

[Documentation for Symfony 1.x](https://symfony.com/legacy/doc).

Most `yml` configuration changes are picked up automatically. In some cases such as adding a new php class, the config needs to be rebuilt. From the `web` bash (`dc exec web bash`):

    sf cache:clear --type=config


## Checking for errors

From the `web` container:

    tail -f /var/log/apache2/error.log

Or use the bash alias (see .docker/php-apache/root/.bash_aliases):

    phperrlog


## Generate favicons (optional)

Should you want to build/rebuild them, follow instructions in [src/web/favicons/README.md](src/web/favicons/README.md).


## Juicer parse errors

**Juicer** is a legacy CSS/JS bundler which was developed for Kanji Koohii. Juicer can bundle lots of CSS/JS components together and substitute custom variables in the output.

When `*.juicy.(css|js)` files don't load properly, view source in browser and click the file. Somewhere the file will be truncated, and an error message would appear:

    Error HTTP 500: ***EXCEPTION*** Could not create folder /home/.../web/build/yui2

Make sure these folders are writable:

    chmod 777 web web/build

If there is no error message, but the css/js doesn't output completely, then run Juicer from the command line to see what is happening, eg:

    php lib/juicer/JuicerCLI.php -v --webroot web --config apps/koohii/config/juicer.config.php --infile web/revtk/main.juicy.css
