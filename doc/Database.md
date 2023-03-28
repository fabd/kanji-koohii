# About the Sample Database

## Users

All users in the sample database have a `test` password (sha encrypted) and a dummy email.

Users are included for integrity between tables. They are real Kanji Koohii user names, but only the public data (shared stories, and profile info) is included.

Additional users can be created on the command line (php container):

```bash
$ php batch/admin/createuser.php -u aaaaaa -p aaaaaa --location 'Localhost'
```

### Admin

The `admin` user displays a log of SQL queries at the bottom of the page.

## Stories

To get a meaningful testing environment, shared stories are included for 2200 RTK kanji (6th edition). Each Study page includes 3 "top voted" stories, and 2 random unvoted stories. There are no private stories in the database, and the shared stories is actually only 7% of the total storage so performance in local development is completely different than production!

## Dictionary (JMDICT related data)

The sample database also includes data parsed from JMDICT. You can experiment with queries if you like but keep in mind the data was designed purely for Kanji Koohii features, and tables are structured only with Kanji Koohii queries in mind. There are much more complete database versions of JMDICT out there.

In any case, see
`src/data/schemas/jdict_schema.sql` and
`src/data/schemas/jdict_queries.sql`.

## Dictionary Lookup Cache

To speed up queries (avoid too many JOINs etc) JMDICT lookup is pre-parsed and output to JSON in `cache_dict_lookup` table.

> :point_right: &nbsp; Note the results cached are not exhaustive. See the script's top comment block for more details.

(Optional) Try this to see what the output data looks like (from php/apache container):

    php data/scripts/dict/dict_gen_cache.php --limit 1 -v -g

To fully populate the table run this _last_ (the above outputs an incomplete set):

    php data/scripts/dict/dict_gen_cache.php -g --limit 5000

## LICENSE

### Kanji Stories

All kanji mnemonics provided in this repository are licensed under [CC BY-NC-SA 3.0](https://creativecommons.org/licenses/by-nc-sa/3.0/) (Attribution-NonCommercial-ShareAlike 3.0 Unported).

**The preferred way to use Koohii stories** and support Koohii at the same time, is to have a link back to the corresponding Study pages eg. "More stories at Kanji Koohii". Display 1-3 stories and allow user to browse additional ones.

**Note:** _the links to a study page must use the UTF-8 character!_ Framenumbers are no longer supported because they change between editions and potentially even additional sequences in the future (such as KKLD). A correct URL to a Study page looks like this:

    https://kanji.koohii.com/study/kanji/一     CORRECT

    https://kanji.koohii.com/study/kanji/1      INCORRECT

### Remembering the Kanji (RTK) Index & Keywords

The database also includes RTK index and keywords as used by [Kanji Koohii](https://kanji.koohii.com). Permission was requested and granted to Fabrice Denis for use on Kanji Koohii (previously "Reviewing the Kanji") by James W. Heisig, author of "Remembering the Kanji", to use the RTK index and keywords. The database provided in this repository includes RTK index and keywords for development purposes only, and the permission to use them does not extend to derived works based on this public repository and its data files (ie. contact the RTK author & publisher).

### KANJDIC & JMDICT data

Much of the other data provided in the sample database is derived from JMDICT and KANJIDIC. This data is the property of the Electronic Dictionary Research and Development Group at Monash University, and should be used in conformance with the Group's [licence](http://www.edrdg.org/edrdg/licence.html).
