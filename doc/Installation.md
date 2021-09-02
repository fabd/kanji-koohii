1. [First Time Setup](#first-time-setup)
2. [Running the website locally](#running-the-website-locally)
3. [Development & Production Builds](#development--production-builds)
   1. [The Symfony 1 Project Structure](#the-symfony-1-project-structure)
4. [Working with the Php-Apache container](#working-with-the-php-apache-container)
   1. [Environments](#environments)
   2. [Using Webpack](#using-webpack)
   3. [Using virtual host name](#using-virtual-host-name)
5. [Working with the MySQL container](#working-with-the-mysql-container)
      1. [About the sample database](#about-the-sample-database)
      2. [MySQL Workbench](#mysql-workbench)
      3. [Using MySQL from the command line](#using-mysql-from-the-command-line)
      4. [Rebuild / reset the sample database](#rebuild--reset-the-sample-database)
6. [F.A.Q.](#faq)
   1. [Docker](#docker)
   2. [Developing with Symfony 1.x](#developing-with-symfony-1x)
   3. [Checking for errors](#checking-for-errors)
   4. [Generate favicons (optional)](#generate-favicons-optional)

# First Time Setup

Pre-requisites:

* Install [Docker CE](https://docs.docker.com/install/) on Ubuntu, or [Docker Desktop](https://docs.docker.com/desktop/mac/install/) on Macs.
* `docker` and `docker-compose` are available in your terminal

**Init bash history files** which are persisted through Docker volumes:

    touch docker/db/bash/.bash_history docker/php-apache/bash/.bash_history

**Build the containers**.

> :point_right: &nbsp; *Note right after the containers are up, MySQL/Mariadb may take a minute to populate the local database. Check with `docker-compose logs -f`. A new folder `mysql56/` will be created in the root directory, which maintains the state of the database.*

    docker-compose up -d

**CLI into the `web` (php/Apache) container**. You should see a colored prompt: `[php]  root /var/www/html $`.

> :point_right: &nbsp; *Note: the path `/var/www/html` is mapped to `src/` which is the Symfony root folder*.

    docker-compose exec web bash

**Init some Symfony directories**:

    mkdir -p cache log ; chmod 777 cache log

**Init the ViteJs build** directory:

    mkdir -p web/build ; chmod 777 web web/build

**Also init some private config files**:

    cp web/.htaccess_default web/.htaccess
    cp apps/koohii/config/app.example.yml apps/koohii/config/app.yml
    cp apps/koohii/config/settings.example.yml apps/koohii/config/settings.yml

**Install node packages** (ignore warnings about "fsevents" and "ajv"):

    npm install

# Running the website locally

Remember to start the **Vite dev server**, otherwise the site will look broken without any stylesheets applied.

:point_right: &nbsp; *Note: if the latency from Vite dev server is annoying it's possible to use `vite build --watch` instead. See `USE_DEV_SERVER` info in [Development.md](./Development.md)*

    cd vite/
    npm run dev

Now you should be able to preview the site at (refresh the page if it looks broken):

    http://localhost/index_dev.php

:point_right: Note: if somehow your `vite` dev server is not running at `http://localhost:3000/` then edit `VITE_SERVER` in `coreWebResponse.php`

You can sign in as `admin`, or `guest` or any of the users that are linked to the shared stories. All users in the sample database have a the password `test` and a dummy email.

You can of course create additional accounts. No emails are sent in development mode.


# Development & Production Builds

Production scripts are not included.

For the produciton experience, use `USE_DEV_SERVER = false` with `vite build --watch`.


Please note Kanji Koohii's code base goes back to the summer of 2005!

**Php**: The backend code started as plain php files. It was later refactored to Symfony 1.x (with some remaining small hacks / tweaks). The php code was updated to run on php 7. In general newer developments should use less php templating and more client side code.

**Docker setup**: we use two simple containers: `web` for Php 7.x & Apache, `db` for MysQL 5.6. For convenience, both containers maintain bash history and custom aliases through a Docker volume (see `docker-compose.yml`).


## The Symfony 1 Project Structure

Some bootstrap code is in `apps/koohii/config/koohiiConfiguration.class.php`.

All application configuration is in `apps/koohii/config/` Yaml files: `app.yml`, `settings.yml`, `routing.yml`, etc.

The global layout is in `apps/koohii/templates/layout.php`.


# Working with the Php-Apache container

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


# Working with the MySQL container

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


# F.A.Q.

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
