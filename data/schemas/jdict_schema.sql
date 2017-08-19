-- ----------------------------------------------------------------------------
--
-- Up to date schema for the dictionary related features.
--
-- 
-- PURPOSE
--
--    Keep in mind Kanji Koohii is not a reference like jisho.org (for example), therefore
--    the database is designed to provide necessary features and nothing else.
--
--    The schema is realtively simple to avoid using too many JOINS. That said I am not
--    a database wizard by any means, and any suggestions to improve the schema are
--    welcome. Performance is always the main concern. On the live site, queries
--    need to run FAST.
--
--
-- TABLES
--
--    jdict
--    dictprons
--    dictsplit
--    dictlevels
--
--
-- BUILD
--
--   Create data files:
--
--     $ cd data/scripts/dict
--     $ php split_readings.php > split_readings.utf8
--     $ perl parse_jdict.pl > table_jdict.utf8
--
--   The folder should now contain data files:
--
--     split_readings.utf8
--     table_jdict.utf8
--     table_dictprons.utf8
--     table_dictsplit.utf8
--
--   Source this script
--
--     mysql> source ../../schemas/jdict_schema.sql;
--
-- ----------------------------------------------------------------------------

-- ----------------------------------------------------------------------------
-- Set database character set & collation
-- ----------------------------------------------------------------------------

ALTER DATABASE DEFAULT CHARACTER SET 'utf8';

-- ----------------------------------------------------------------------------
-- Do this if LOAD DATA creates multiple ascii characters instead of kanji
-- ----------------------------------------------------------------------------

SET NAMES 'utf8';


-- ----------------------------------------------------------------------------
-- jdict
-- ----------------------------------------------------------------------------
-- 
-- This is a simple implementation of JMDICT, which uses newly generated ent_seq
-- id's (dictid) for multiple compound/reading combinations that belong to the
-- same "gloss".
-- 
-- The key goal for Trinity was to make sure that the compound ids
-- do not change if feeding the database with a new version of JMDICT.
-- To handle the newly generated ids, there would be a remapping of old ids to
-- new ids, by using the unique compound/reading combinations as the link
-- (never tested/implemented)
--
-- 
--  dictid     Unique id derived from ent_seq (but not necessarily sequential)
--  pri        Priority (news1, news2, ichi1, ... as a bitmask)
--  pos        Bitmask (see "dictpos" constants in rtkLabs.php)
--  verb       Bitmask (see "dictverb" constants in rtkLabs.php)
--  misc       Bitmask (see "dictmisc" constants in rtkLabs.php)
--  field      Bitmask (see "dictfield" constants in rtkLabs.php)
--  compound   
--  reading
--  glossary   Contains all the glosses separated by ;
--  
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS jdict;

CREATE TABLE `jdict` (
  `dictid`     mediumint(8) unsigned NOT NULL DEFAULT '0',
  `pri`        tinyint(3) unsigned NOT NULL DEFAULT '0',
  `pos`        int(10) unsigned NOT NULL DEFAULT '0',
  `verb`       int(10) unsigned NOT NULL DEFAULT '0',
  `misc`       int(10) unsigned NOT NULL DEFAULT '0',
  `field`      int(10) unsigned NOT NULL DEFAULT '0',
  `compound`   tinytext NOT NULL,
  `reading`    tinytext NOT NULL,
  `glossary`   text NOT NULL,
  PRIMARY KEY (`dictid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES jdict WRITE;
LOAD DATA LOW_PRIORITY LOCAL INFILE "table_jdict.utf8" INTO TABLE jdict CHARACTER SET 'utf8' LINES TERMINATED BY '\n';
UNLOCK TABLES;


-- ----------------------------------------------------------------------------
-- dictprons
-- ----------------------------------------------------------------------------
-- This table contains all unique pronunciations referenced by dictsplit.
-- 
--  pronid         Unique key
--  pron           kana string (ordered by a hiragana_sort)
-- 
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS dictprons;

CREATE TABLE `dictprons` (
  `pronid`     smallint(5) unsigned NOT NULL DEFAULT '0',
  `pron`       char(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`pronid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES dictprons WRITE;
LOAD DATA LOW_PRIORITY LOCAL INFILE "table_dictprons.utf8" REPLACE INTO TABLE dictprons CHARACTER SET 'utf8' LINES TERMINATED BY '\n';
UNLOCK TABLES;


-- ----------------------------------------------------------------------------
-- dictsplit
-- ----------------------------------------------------------------------------
-- 
-- This table contains the furigana information for JMDICT entries.
--
--  dictid         => jdict.dictid
--  kanji          Unicode code point (16bit value)
--                 Use CHAR(kanji USING "ucs2") to get the utf8 character in MySQL
--  pronid         => dictprons.pronid
--  type           0 = kana, 1 = ON, 2 = KUN
--  position       Zero-based index in compound
-- 
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS dictsplit;

CREATE TABLE `dictsplit` (
  `dictid`     mediumint(8) unsigned NOT NULL DEFAULT '0',
  `kanji`      smallint(5) unsigned NOT NULL DEFAULT '0',
  `pronid`     smallint(5) unsigned NOT NULL DEFAULT '0',
  `type`       tinyint(4) NOT NULL DEFAULT '0',
  `position`   tinyint(3) unsigned NOT NULL DEFAULT '0',
  -- `numkanji`   tinyint(3) unsigned NOT NULL,
  -- `pri`        tinyint(3) unsigned NOT NULL,
  KEY (`dictid`),
  KEY (`kanji`),
  KEY (`pronid`)
  -- KEY `onlyknownkanji` (`kanji`,`dictid`,`numkanji`,`pri`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES dictsplit WRITE;
LOAD DATA LOW_PRIORITY LOCAL INFILE "table_dictsplit.utf8" INTO TABLE dictsplit LINES TERMINATED BY '\n';
UNLOCK TABLES;

--
-- add `numkanji` to dictsplit
-- 
ALTER TABLE dictsplit ADD `numkanji` TINYINT UNSIGNED NOT NULL;
CREATE TABLE numkanji(SELECT dictid, COUNT(*) AS c FROM dictsplit WHERE type > 0 GROUP BY dictid);

ALTER TABLE numkanji ADD PRIMARY KEY (`dictid`); -- speeds up update
UPDATE dictsplit JOIN numkanji USING (dictid) SET dictsplit.numkanji = numkanji.c;

DROP TABLE numkanji;

--
-- add `pri` to dictsplit
-- 
ALTER TABLE dictsplit ADD `pri` TINYINT UNSIGNED NOT NULL;
UPDATE dictsplit JOIN jdict USING (dictid) SET dictsplit.pri = jdict.pri;

--
-- add `onlyknownkanji` index that contain the data we need so we can get 0.20s query with `pri` filter
-- 
ALTER TABLE dictsplit ADD KEY `onlyknownkanji` (kanji, dictid, numkanji, pri);



-- ----------------------------------------------------------------------------
-- dictlevels
-- ----------------------------------------------------------------------------
-- This table is GENERATED from jdict and dictsplit, and provides an index 
-- that allows to find quickly kanji compounds using only characters up to
-- a given Heisig index number.
--
--  dictid       => jdict.dictid
--  framenum     max(RTK frame num) for all kanji in given jmdict entry
--  pri          => jdict.pri  (speeds up query)
--
--
-- CREDITS
--
--   Thanks to github.com/nikitakit for the original solution! (April 2010)
--
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS dictlevels;

CREATE TABLE `dictlevels` (
  SELECT jdict.dictid, MAX(k.idx_newed) AS `framenum`
    FROM jdict
    JOIN dictsplit  ds ON jdict.dictid = ds.dictid
    JOIN kanjis k ON ds.kanji = k.ucs_id
    GROUP BY jdict.dictid
);

-- ALTER TABLE `dictlevels` CHANGE `framenum` smallint(5) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `dictlevels` ADD PRIMARY KEY (`dictid`);

--
-- ADD KEY  to optimize VocabShuffle's "Nikitakit" mode (pick random words using RTK kanji up to index N).
--
-- Speed query test:
--
--   SELECT dl.dictid FROM dictlevels dl WHERE framenum <= 2000 AND pri & 0xE0 ORDER BY rand() LIMIT 20;
-- 
ALTER TABLE dictlevels ADD `pri` TINYINT UNSIGNED NOT NULL;
UPDATE dictlevels dl JOIN jdict jd USING (dictid) SET dl.pri = jd.pri;
ALTER TABLE dictlevels ADD KEY `vocabshuffle_query` (dictid, framenum, pri);

