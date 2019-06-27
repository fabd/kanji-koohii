# Table of Contents
1. [Installation](#install)
2. [About the project](#about)
3. [Working with the Php-Apache container
](#webserver)
4. [Working with the MySQL container](#database)
5. [Troubleshooting](#troubleshooting)
6. [Useful Tips & Aliases](#tips)


# Installation <a name="install"></a>

## Requirements

Install [Docker CE](https://docs.docker.com/install/).

##  First Time Setup

Setup files for docker volumes that persist bash history in the containers:

    touch docker/db/bash/.bash_history docker/php-apache/bash/.bash_history

Build the containers:

    docker-compose up -d

*Note right after the containers are up, MySQL may take a minute to setup the local database (check with `docker-compose logs -f`). A new folder `mysql56/` will be created in the root directory, which maintains the state of the database.*

Start bash from the *webserver* container:

    docker-compose exec webserver bash

You should see a colored prompt: `[php] root /var/www/html $`.

*Note: the path `/var/www/html` corresponds to the Symfony root folder found in `src/`.*

From the webserver bash, init some directories:

    mkdir -p cache log ; chmod 777 cache log
    mkdir -p web/build ; chmod 777 web web/build

Also init some private config files:

    cp web/.htaccess_default web/.htaccess
    cp apps/koohii/config/app.example.yml apps/koohii/config/app.yml
    cp apps/koohii/config/settings.example.yml apps/koohii/config/settings.yml

Init node packages (ignore warnings about "fsevents" and "ajv"):

    npm install

Build the Vue bundles:

    npm run dev

You should see something like this (files are output to `src/web/build/pack/`):

    Version: webpack 3.12.0
    Time: 10562ms
                   Asset     Size  Chunks                    Chunk     Names
    review-bundle.raw.js   159 kB       0  [emitted]             review-bundle
     study-bundle.raw.js   116 kB       1  [emitted]             study-bundle
      root-bundle.raw.js   389 kB       2  [emitted]  [big]      root-bundle
         root-bundle.css  1.86 kB       2  [emitted]             root-bundle
        study-bundle.css  6.67 kB       1  [emitted]             study-bundle
       review-bundle.css    15 kB       0  [emitted]             review-bundle
    


**Done!**

You should be able to see the homepage: http://localhost/index_dev.php

Sign in with user `guest` pw `test` (or create an account, no emails are sent in development mode).


# About the project <a name="about"></a>

Please note Kanji Koohii started 12+ years ago! The project is based on Symfony 1.4-ish (with some tweaks here and there).

**Docker Setup**: we use two simple containers: `webserver` for Php 7.0 & Apache, `db` for MysQL 5.6. For convenience, both containers maintain bash history and custom aliases through a Docker volume (see `docker-compose.yml`).

**The legacy build** compiles object-oriented Javascript, along with YUI2 library. See `src/batch/build.sh`. You don't need to build in development because the legacy css/js has "hot reload" through a rewrite rule in the .htaccess file. You can just edit the old css/js files and refresh the page (with 'Disable cache' in your Chrome Console). The build script is only for deployment (bundling & minifying of the older assets).

**The Vue build** was introduced in recent years. It uses Webpack, Babel & VueJS. Hence, new developments can use modern Javascript. Newer css/js (single file components), are located in `src/lib/front/vue/`. The Vue build does *not* feature hot reload: you must use `npm run dev` after editing Vue components & bundles.


## The Symfony 1 Project Structure

Some bootstrap code is in `apps/koohii/config/koohiiConfiguration.class.php`.

All application configuration is in `apps/koohii/config/` Yaml files: `app.yml`, `settings.yml`, `routing.yml`, etc.

The global layout is in `apps/koohii/templates/layout.php`.


# Working with the Php-Apache container <a name="webserver"></a>

## User accounts

All users in the test database have a 'test' password.

* `admin` displays a log of SQL queries at the bottom of the page
* `guest` is a regular user account

You can create additional users from the command line. From the *webserver* type:

    $ php batch/maintenance/createUser.php
    

## Development & test builds

Development build is the default environment accessed via `index.php` or `index_dev.php`:

* **The legacy build** has "hot reload" through custom tool "Juicer" (see `batch/build.sh` for more info). Since Juicer is run through a mod rewrite rule, simply check the "Disable cache" in Chrome Dev Tools, and refresh/reload the page. Any changes to `.juicy.(css|js)` files will be picked up!

* **The VueJS & ES6 build** is not configured with hot reload. Even in development, changes to the Vue components and bundles have to be recompiled with `npm run dev`.

### Test environment

The test environment is accessed via `index_test.php`.

The test build is similar to production. It bypasses Juicer and requests minified css/js assets. It's useful to see the performance via browser dev tools "Network" tab: size of minified assets, number of requests, etc.

Since *test* bypasses Juicer, there is no "hot loading" and we have to build legacy assets + Vue *production* assets. All test/production assets are compiled (and deployed from) `web/build/`:

    $ batch/build
    $ npm run build


## Using virtual host name

You can use a virtual host name instead of `localhost`, for example (`/etc/hosts`):

    127.0.0.1    koohii.local

To access with: http://koohii.local

Make sure it matches the `ServerName` in `.docker/php-apache/koohii.vhosts.conf`, then rebuild the *webserver* container (`dc down ; dc build ; dc up -d`).



Also make sure to update `website_url` setting
located in `src/apps/koohii/config/app.yml`. This setting is used to generate links in a few places.


# Working with the MySQL container <a name="database"></a>

The local database is initialized from a SQL file located in `.docker/db/initdb.d/`. This folder is mapped to the MySQL Docker image (cf.  [Initializing a fresh instance](https://hub.docker.com/_/mysql/#initializing-a-fresh-instance)).

The database state itself is maintained through a volume. The first time you run the *db*  service, a `mysql56` folder will appear in the root directory on your host. If you delete this folder, any changes like new user accounts, stories and flashcards will be lost.

### MySQL Workbench

You can use [MySQL Workbench](https://dev.mysql.com/downloads/workbench/) on your host to run queries through a GUI. Use `localhost` and port `3306`.

### Using MySQL from the command line

To use mysql CLI, start bash from the *db* container:

    dc exec db bash
    [mysql] root /etc/mysql $ 

Then run **mysql** CLI:

    mysql -u koohii -pkoohii -h localhost -D db_koohii --default-character-set=utf8

You can also create and use **aliases** from `.docker/db/root/.bashrc`:

    dc exec db bash
    [mysql] root /etc/mysql $ koohii-db
    mysql> show tables;
    +---------------------+
    | Tables_in_db_koohii |
    +---------------------+
    | active_guests       |
    | ...


## Optional



## F.A.Q.


### Docker

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

    dc down
    dc up -d

You can also check the logs:

    dc logs -f


### Developing with Symfony 1.x

[Documentation for Symfony 1.x](https://symfony.com/legacy/doc).

Most `yml` configuration changes are picked up automatically. In some cases such as adding a new php class, the config needs to be rebuilt. From the `webserver` bash (`dc exec webserver bash`):

    sf cache:clear --type=config


### Checking for errors

From the webserver container:

    tail -f /var/log/apache2/error.log

Or use the bash alias (see .docker/php-apache/root/.bash_aliases):

    phperrlog


### Generate favicons (optional)

Should you want to build/rebuild them, follow instructions in [src/web/favicons/README.md](src/web/favicons/README.md).


# Troubleshooting <a name="troubleshooting"></a>

## Juicer parse errors
When `*.juicy.(css|js)` files don't load properly, view source in browser and click the file. Somewhere the file will be truncated, and an error message would appear:

    Error HTTP 500: ***EXCEPTION*** Could not create folder /home/.../web/build/yui2

Make sure these folders are writable:

    chmod 777 web web/build

If there is no error message, but the css/js doesn't output completely, then run Juicer from the command line to see what is happening, eg:

    php lib/juicer/JuicerCLI.php -v --webroot web --config apps/koohii/config/juicer.config.php --infile web/revtk/main.juicy.css


# Useful Tips & Aliases

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

