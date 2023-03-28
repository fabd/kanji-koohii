# Incremental updates

The sample database is a snapshot that includes all the incremental updates found in `src/data/schemas/incremental/` up to and including `rtk_0013`.

Additional incremental updates to be applied to the sample database, should be copied from  `src/data/schemas/incremental/` to `docker/db/initdb.d/`.

As per the official MySQL docker image (see [Initializing a fresh instance](https://hub.docker.com/_/mysql)), the SQL files found in `/docker-entrypoint-initdb.d` will be executed in filename alphabetical order (which we mapped to /initdb.d/ in docker-compose.yml).

So the SQL files would be applied like this, with the sample database snapshot first:

1. koohii_dev_0013.sql.gz
2. rtk_0014_srs_settings.sql
3. rtk_0015_fix_location_encoding.sql
4. (etc)

## Resetting the database

If I forgot to keep the folder up to date somehow, double check for missing incremental  update files from:

    src/data/schemas/incremental/

Copy the missing files (*after* rtk_0013) to `docker/db/initdb.d/`.

Then recreate the db container like so, which will rebuild the sample database with all the updates:

```bash
sudo rm -rf mysql56
docker compose down
docker compose build db
docker compose up -d
```

You can confirm the SQL updates that were applied if you quickly run `docker compose logs -f` after using `up -d`.

## Updating the schema

The simplest way to apply SQL scripts is to copy them in `docker/db/initdb.d/`, since this folder is mapped in docker-compose.yml.

Then you can SOURCE them from the db container, for example:

```bash
docker compose exec db bash
$ mysql -u koohii -pkoohii -D db_github --default-character-set=utf8
mysql> SOURCE /initdb.d/example_file.sql
```
