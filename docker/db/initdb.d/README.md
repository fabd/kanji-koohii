# Incremental updates

Incremental SQL updates are concatenated to a single SQL script in this folder, to be applied on top of the last dev database dump.
As per MySQL official docker image, SQL files found in
`/docker-entrypoint-initdb.d`
 (cf. volume in docker-compose.yml) are applied in alphabetical order, hence:

1. koohii_dev_00xx.sql.gz
2. koohii_dev_updates.sql

If we forgot to update this file somehow, look for any other incremental
updates in `src/data/schemas/incremental/` and apply the ones missing in
order.

Snippet that generated `koohii_dev_update.sql` (2019-03-11):

    cat src/data/schemas/incremental/rtk_0014_srs_settings.sql \
    src/data/schemas/incremental/rtk_0015_fix_location_encoding.sql \
    src/data/schemas/incremental/rtk_0016_vocabpicks.sql \
    src/data/schemas/incremental/rtk_0017_dict_results_cache.sql \
    > docker/db/initdb.d/koohii_dev_update.sql



## Rebuild dev database

To reset the database:

    $ sudo rm -rf mysql56

Then rebuild the MySQL container:

    $ docker-compose down
    $ docker-compose build
    $ docker-compose up -d
