1. [First Time Setup](#first-time-setup)
2. [Running the website](#running-the-website)
3. [Development & Production Builds](#development--production-builds)
   1. [The Symfony 1 Project Structure](#the-symfony-1-project-structure)
4. [The Php/Apache container](#the-phpapache-container)
   1. [Solving file permission issues with a Linux host](#solving-file-permission-issues-with-a-linux-host)
   2. [Using virtual host name](#using-virtual-host-name)
5. [The MySQL/mariadb container](#the-mysqlmariadb-container)
      1. [About the sample database](#about-the-sample-database)
      2. [MySQL Workbench](#mysql-workbench)
      3. [Using MySQL from the command line](#using-mysql-from-the-command-line)
      4. [Rebuild / reset the sample database](#rebuild--reset-the-sample-database)
6. [Working with Symfony 1.x](#working-with-symfony-1x)
   1. [Clearing Symfony's cache](#clearing-symfonys-cache)
7. [F.A.Q.](#faq)
   1. [VSCode Setup](#vscode-setup)
   2. [Docker](#docker)
   3. [Generate favicons (optional)](#generate-favicons-optional)

# First Time Setup

**Pre-requisites**

- Install [Docker Engine](https://docs.docker.com/install/) on Ubuntu, or [Docker Desktop](https://docs.docker.com/desktop/mac/install/) on Macs.
- `docker` and `docker compose` are available in your terminal

**Init bash history files** which are persisted through Docker volumes:

    touch docker/db/.bash_history docker/php/.bash_history

**Build the containers**.

> :point_right: &nbsp; Note right after the containers are up, MySQL/Mariadb may take a minute to populate the local database. Check with `docker compose logs -f`. A new folder `mysql56/` will be created in the root directory, which maintains the state of the database.

    docker compose up -d

**CLI into the `web` (php/Apache) container**. You should see a colored prompt: `[php] root /var/www/html $`.

> :point_right: &nbsp; Note: the path `/var/www/html` is mapped to `src/` which is the Symfony root folder - while `/var/www/html/web` is the folder served by Apache.

    docker compose exec web bash

**Init some application config files and folders**:

    mkdir -p cache log web/build && chmod 777 cache log web web/build
    cp web/.htaccess_default web/.htaccess
    cp apps/koohii/config/app.example.yml apps/koohii/config/app.yml
    cp apps/koohii/config/settings.example.yml apps/koohii/config/settings.yml

**Install php packages with composer**:

The next step will create and populate the `src/vendor/` subfolder:

> :point_right: &nbsp; Note! If you are adding a new php class, you may have to run `composer dump-autoload` to pick up the new files.

    composer install

**Install node packages**:

> :point_right: &nbsp; Note how **npm** scripts are run from the **vite/** subfolder -- which is NOT the Symfony root folder

    cd vite/
    npm ci

# Running the website

CLI into the php/Apache container :

    docker compose exec web bash

Then start the **Vite dev server**:

> :point_right: &nbsp; `vite` is an alias set up  in `./docker/bash/` - it is the same as `npm run dev`

> :point_right: &nbsp; If the latency from Vite dev server is annoying it's possible to use `vite build --watch` instead. See `USE_DEV_SERVER` info in [Development.md](./Development.md)

    cd /var/www/html/vite/ && vite

You should see something like this:

> :point_right: &nbsp; Note: if somehow your `vite` dev server is not running on the same port then edit `VITE_SERVER` in `coreWebResponse.php`

    VITE v4.2.1  ready in 282 ms
  
    ➜  Local:   http://localhost:5173/
    ➜  Network: http://172.18.0.3:5173/
    ➜  press h to show help

Now you should be able to preview the site at (refresh the page if it looks broken):

    http://localhost/index_dev.php


You can sign in as `admin`, or `guest` or any of the users that are linked to the shared stories.

* All users in the sample database have the password `test` and a dummy email.
* You can create additional test users using the signup form, or on the command line with the `createuser` script (see the alias in docker/bash/koohii_dev.sh).
* No emails are sent in development mode.

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

# The Php/Apache container

This is how I usually work with the docker locally:

- I run VSCode, Sublime Merge, Vim, git etc. **from the host**
- all `npm` scripts, including the Vite client are run **from the container**

## Solving file permission issues with a Linux host

On a Linux host there may be file permission issues with the `/src` folder. The file permissions are "imported" by the docker volume. To solve this a custom user is created with uid/gid that matches the permissions of your user in the Linux host. Apache is then configured to run as that user (instead of the default `www-data`).

If you are seeing php errors related to writing to files - check your uid/gid with the `id` command (in the host), and make sure it matches those in the Dockerfile (then rebuild the container with `dc down ; dc build ; dc up -d`):

    ARG UID=1000
    ARG GID=1000



## Using virtual host name

You can use a virtual host name instead of `localhost`, for example (`/etc/hosts`):

    127.0.0.1    koohii.local

Update the `ServerName` in `.docker/php/koohii.conf`, then rebuild the `web` container (`dc down ; dc build ; dc up -d`).

Also make sure to update `website_url` setting located in `src/apps/koohii/config/app.yml`. This setting is used to generate links in a few places.

# The MySQL/mariadb container

The local database is initialized from a SQL file located in `.docker/db/initdb.d/`. This folder is mapped to the MySQL Docker image (cf. [Initializing a fresh instance](https://hub.docker.com/_/mysql/#initializing-a-fresh-instance)).

The database state itself is maintained through a volume. The first time you run the _db_ service, a `mysql56` folder will appear in the root directory on your host. If you delete this folder, any changes like new user accounts, stories and flashcards will be lost.

### About the sample database

See [Database.md](./Database.md) in doc/ folder.

### MySQL Workbench

You can use [MySQL Workbench](https://dev.mysql.com/downloads/workbench/) on your host to run queries through a GUI. Use `localhost` and port `3306`.

### Using MySQL from the command line

To use mysql CLI, start bash from the `db` container:

    dc exec db bash
    [mysql] root /etc/mysql $

Then run **mysql** CLI:

    mysql -u koohii -pkoohii -h localhost -D db_github --default-character-set=utf8

You can also use one of the aliases declared in `.docker/bash/bashrc`.

### Rebuild / reset the sample database

Source files are in `docker/db/initdb.d/`.

Should you need to reset the database:

    $ sudo rm -rf mysql56

Then rebuild the MySQL container:

    $ dc down ; dc build ; dc up -d

# Working with Symfony 1.x

Kanji Koohii is still running an outdated version fo Symfony. Unfortunately at this point it would take a LOT of work to refactor it. That said, SF 1.x is pretty easy to use.

The [FriendsOfSymfony1](https://github.com/FriendsOfSymfony1/symfony1) community-driven fork has been merged, which provides support for PHP 7.

- [The Definitive Guide (1.2)](https://symfony.com/legacy/doc/book/1_2) is very good for a quick overview
- [Documentation for Symfony 1.x](https://symfony.com/legacy/doc)

## Clearing Symfony's cache

Most YAML (`.yml`) configuration changes are picked up automatically (for example, adding a javascript asset to one of the `view.yml` files). In some cases such as adding a new php class, Symfony's autoloader may not pick it up. In this case you'll want to manually rebuild the cache. Run the following command from the `web` (php/apache) container:

> :exclamation: &nbsp; Make sure to be in the Symfony root folder `src/`, **not** in the Vite subfolder (`src/vite/`)

    sf cache:clear --type=config

# F.A.Q.

## VSCode Setup

Recommended setup:

- ESLint [link](https://marketplace.visualstudio.com/items?itemName=dbaeumer.vscode-eslint)
- Prettier [link](https://marketplace.visualstudio.com/items?itemName=esbenp.prettier-vscode)
- Stylelint [link](https://marketplace.visualstudio.com/items?itemName=stylelint.vscode-stylelint)
- Tailwind CSS IntelliSense [link](https://marketplace.visualstudio.com/items?itemName=bradlc.vscode-tailwindcss)
- TypeScript Vue Plugin (Volar) [link](https://marketplace.visualstudio.com/items?itemName=Vue.vscode-typescript-vue-plugin)
- Vue Language Features (Volar) [link](https://marketplace.visualstudio.com/items?itemName=Vue.volar)

## Docker

Suggested aliases:

    alias dc='docker compose'
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

## Generate favicons (optional)

Should you want to build/rebuild them, follow instructions in [src/web/favicons/README.md](src/web/favicons/README.md).
