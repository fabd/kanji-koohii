--
-- Date: 2018.10
--
-- This table links vocab (JMDICT entry id) to kanji cards (userid, ucs_id).
--

-- LOCK TABLES users WRITE;


-- ----------------------------------------------------------------------------
-- vocabpicks
-- ----------------------------------------------------------------------------
--  userid        : from users table
--  ucs_id        : UCS-2 code value (16 bit).
--  dictid        : cf. jdict_schema

-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS vocabpicks;

CREATE TABLE `vocabpicks` (

  `userid`       MEDIUMINT(4) UNSIGNED NOT NULL,
  `ucs_id`       SMALLINT UNSIGNED NOT NULL,
  `dictid`       mediumint(8) unsigned NOT NULL DEFAULT '0',

  `created_on`   TIMESTAMP NOT NULL DEFAULT 0,

  PRIMARY KEY  (`userid`, `ucs_id`)

) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- KEY `created_on` (`created_on`),

-- UNLOCK TABLES;