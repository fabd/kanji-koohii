--
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