# Table of Contents
1. [First Time Setup](#install)
2. [Development & Production Builds](#about)
3. [Working with the Php-Apache container
](#webserver)
4. [Working with the MySQL container](#database)
5. [Useful Aliases](#tips)
6. [F.A.Q.](#faq)


# First Time Setup <a name="install"></a>

Install [Docker CE](https://docs.docker.com/install/).

Setup files for docker volumes that persist bash history in the containers:

    touch docker/db/bash/.bash_history docker/php-apache/bash/.bash_history

Build the containers:

    docker-compose up -d

*Note right after the containers are up, MySQL may take a minute to setup the local database (check with `docker-compose logs -f`). A new folder `mysql56/` will be created in the root directory, which maintains the state of the database.*

Start bash from the `web` container:

    docker-compose exec web bash

You should see a colored prompt: `[php] root /var/www/html $` *(the path `/var/www/html` corresponds to the Symfony root folder found in `src/`)*

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

You should be able to see the homepage: http://localhost/index_dev.php

Sign in with user `guest` pw `test` (or create an account, no emails are sent in development mode).


# Development & Production Builds <a name="about"></a>

Please note Kanji Koohii's code base goes back to the summer of 2005!

**Php**: The backend code started as plain php files. It was later refactored to Symfony 1.x (with some remaining small hacks / tweaks). The php code was updated to run on php 7. In general newer developments should use less php templating and more client side code.

**Docker setup**: we use two simple containers: `web` for Php 7.x & Apache, `db` for MysQL 5.6. For convenience, both containers maintain bash history and custom aliases through a Docker volume (see `docker-compose.yml`).

**Webpack build**: the project is now updated for Webpack 4. Newer developments on the front end side can use modern Javascript, Vue framework, etc.

- **Vue components** are located in `src/lib/front/vue/`.
- All the legacy stylesheets are now included in the Webpack bundles. The legacy styles are plain CSS, but we can use **SCSS**. Legacy CSS bundles moved to `web/koohii/*.scss`
- Use `npm run watch` to automatically recompile css/js bundles
- Use `npm run prod` for the **production build**
  * production uses an additional `vendor` bundle (Vue, etc)
  * production uses extracted css bundles (one per js bundle)
- Use `npm run build:analyze` to analyze the production build (access from outside container with `http://127.0.0.1:8888/`)

**The legacy build**: long before the upgrade to Webpack 4, the scripts & stylesheets were built with a custom tool called Juicer (an asset packaging tool inspired by "Sprockets"). The legacy JS bundles were based on YUI2 framework. As it's very time consuming to refactor, there are still old scripts in use.

- Legacy JS bundles have a `*.juicy.js` naming pattern and are located in `web/revtk/`
- For **development**, the legacy JS bundles are compiled by Juicer via a mod_rewrite rule -- simply refresh the page to see changes
- The **test/production** builds require compiling and minifying legacy JS to static bundles, use `batch/build.sh --all`


## The Symfony 1 Project Structure

Some bootstrap code is in `apps/koohii/config/koohiiConfiguration.class.php`.

All application configuration is in `apps/koohii/config/` Yaml files: `app.yml`, `settings.yml`, `routing.yml`, etc.

The global layout is in `apps/koohii/templates/layout.php`.


# Working with the Php-Apache container <a name="webserver"></a>

## Environments

* development: `index.php` or `index_dev.php`
* test: `index_test.php`


## Using Webpack

This setup assumes files are edited/added/removed from outside the container, otherwise there are annoying permission issues (in Ubuntu, at least).

Typically I run git & git GUIs, as well as grep/ack/etc, from a terminal in the host.

All `npm` commands are run from the container, as well as `batch/build.sh` script.

## Using virtual host name

You can use a virtual host name instead of `localhost`, for example (`/etc/hosts`):

    127.0.0.1    koohii.local

Update the `ServerName` in `.docker/php-apache/koohii.vhosts.conf`, then rebuild the `web` container (`dc down ; dc build ; dc up -d`).

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
              --exclude=*{.min.js,.juiced.js,.min.css} \
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
          --ignore-file=match:/juicy.js$/ \
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

    dc down && dc up -d

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
