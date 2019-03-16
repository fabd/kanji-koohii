--
-- Date: 2017.11
--
-- Add Custom SRS Options
--
--
--  srs_max_box    Leitner box (typically 5 to 10)
--  srs_mult       Multiplier float x.xx stored as integer (eg. 205 = 2.05)
--  srs_hard_box   Leitner box (within srs_max_box range)

ALTER TABLE users_settings ADD srs_max_box  TINYINT  UNSIGNED NOT NULL;
ALTER TABLE users_settings ADD srs_mult     SMALLINT UNSIGNED NOT NULL;
ALTER TABLE users_settings ADD srs_hard_box TINYINT  UNSIGNED NOT NULL;

-- update for existing users
UPDATE users_settings SET updated_on = updated_on, srs_max_box = 7, srs_mult = 205, srs_hard_box = 0;
--
-- Date: 2018.08
--
-- Fix encoding of "location" in user profile to allow for non-english characters.
--  eg. Kraków  México
--

LOCK TABLES users WRITE;

ALTER TABLE users MODIFY `location` VARCHAR(32) CHARACTER SET utf8 NOT NULL DEFAULT '';

-- misc / remove DEFAULT NULL
ALTER TABLE users MODIFY `email` VARCHAR(100) NOT NULL DEFAULT '';

UNLOCK TABLES;--
-- Date: 2018.10
--
-- This table links vocab (JMDICT entry id) to kanji cards (userid, ucs_id).
--

-- ----------------------------------------------------------------------------
-- vocabpicks
-- ----------------------------------------------------------------------------
--  userid        : from users table
--  ucs_id        : UCS-2 code value (16 bit).
--  dictid        : cf. jdict_schema
--
--  updated_on    : note we don't want ON UPDATE since that gives headaches when manually
--                  editing the table, and coreDatabaseTable handles updating these anyway.
--
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS vocabpicks;

CREATE TABLE `vocabpicks` (

  `userid`       MEDIUMINT(4) UNSIGNED NOT NULL,
  `ucs_id`       SMALLINT UNSIGNED NOT NULL,
  `dictid`       mediumint(8) unsigned NOT NULL DEFAULT '0',

  `updated_on`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY  (`userid`, `ucs_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
--  WE ARE PHASING OUT THIS COLUMN -- can remove from dev -- DO NOT REMOVE FROM LIVE DATABASE
--
-- ALTER TABLE users_settings DROP COLUMN show_onkun;
--
-- Date: 2018.10
--
-- This table stores JSON results for dictionary lookups to speed up queries.
--

-- ----------------------------------------------------------------------------
-- cache_dict_lookup
-- ----------------------------------------------------------------------------
--  ucs_id        : UCS-2 code value (16 bit).
--  num_entries   : total of dict entries, 0 means "no priority entries" (not top 16,000 JMDICT words)
--  json          : array of DictEntry results (id, compound, reading, glossary, priority)
--                : (6150 char_length just fits the longest JSON with 50 results / kanji)
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS cache_dict_lookup;

CREATE TABLE `cache_dict_lookup` (

  `ucs_id`       SMALLINT UNSIGNED NOT NULL,
  `num_entries`  SMALLINT UNSIGNED NOT NULL DEFAULT '0',

  `json`         VARCHAR(6150) NOT NULL DEFAULT '',

  PRIMARY KEY  (`ucs_id`)

) ENGINE=MyISAM DEFAULT CHARSET=utf8;

