-- ----------------------------------------------------------------------------
--
-- KANJI KOOHII - Database Schema
--
-- This schema needs to be kept up to date with data/schemas/incremental/*
--
-- ----------------------------------------------------------------------------

-- ----------------------------------------------------------------------------
-- Set database character set & collation
-- (the web host may not allow to use CREATE DATABASE directly)
-- ----------------------------------------------------------------------------

ALTER DATABASE DEFAULT CHARACTER SET 'utf8';

-- ----------------------------------------------------------------------------
-- active_guests
-- ----------------------------------------------------------------------------
-- Simple table that keeps track of recent visitors with unique ips, who are
-- not signed in. The old site footer used to display "30 members, 5 guests" ..
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS active_guests;

CREATE TABLE `active_guests` (
  `ip`           CHAR(15) NOT NULL DEFAULT '',
  `timestamp`    INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY  (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- ----------------------------------------------------------------------------
-- active_members
-- ----------------------------------------------------------------------------
--
-- This table stores information from an active user that can be looked up
-- easily later and save querries, for example on the active members list.
--
-- Not all data is updated at once, and not always in the same order,
-- because of this, the DEFAULT values are what the logic needs to identify
-- information that was not already set for the user.
-- 
-- The main use for this table for now is to allow to revist the flashcard
-- review summary without a POST request. So long as another review was not
-- started, the last review (lastrs_*) info can be used. There is code somewhere
-- that clears this data after a while.
-- 
--  userid
--  fc_count      Flashcard count
--  last_review   Most recent single flashcard review
--  lastrs_start  Last review session start time (TIMESTAMP value) (Review Summary)
--                This integer value must match the lastreview timestamp of the flashcard review table
--  lastrs_pass   Last review session pass count (Review Summary)
--  lastrs_fail   Last review session fail count (Review Summary)
--
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS active_members;

CREATE TABLE `active_members` (
  `userid`       MEDIUMINT(4) UNSIGNED NOT NULL,
  `fc_count`     SMALLINT NOT NULL DEFAULT 0,
  `last_review`  DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastrs_start` INT(10) UNSIGNED NOT NULL,
  `lastrs_pass`  SMALLINT(5) UNSIGNED NOT NULL,
  `lastrs_fail`  SMALLINT(6) UNSIGNED NOT NULL,
  PRIMARY KEY  (`userid`),
  KEY `last_review` (`last_review`),
  KEY `fc_count` (`fc_count`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------------------------------------------------------
-- active_users
-- ----------------------------------------------------------------------------
-- Simple table that keeps track of recently signed in users. It used to show
-- in the footer of the old site, not usre if it is still used..
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS active_users;

CREATE TABLE `active_users` (
  `username`     CHAR(32) NOT NULL DEFAULT '',
  `timestamp`    INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY  (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- ----------------------------------------------------------------------------
-- custkeywords
-- ----------------------------------------------------------------------------
-- Custom keywords that replace the default kanji keywords. Not all
-- framenumbers need be replaced in this table.
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS custkeywords;

CREATE TABLE `custkeywords` (
  `userid`       MEDIUMINT(4) UNSIGNED NOT NULL,
  `ucs_id`       SMALLINT(5)  UNSIGNED NOT NULL,
  `keyword`      VARCHAR(32) NOT NULL DEFAULT '',
  `created_on`   TIMESTAMP NOT NULL DEFAULT 0,
  `updated_on`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (`userid`, `ucs_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------------------------------------------------------
-- kanjis
-- ----------------------------------------------------------------------------
-- Perl script not in repo yet, load data from the data/table_kanjis.utf8
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS kanjis;

CREATE TABLE `kanjis` (
  `ucs_id`       SMALLINT UNSIGNED NOT NULL,
  `keyword`      CHAR(32) NOT NULL DEFAULT '',
  `kanji`        CHAR(1) NOT NULL DEFAULT '',
  `onyomi`       VARCHAR(50) NOT NULL DEFAULT '',
  `idx_olded`    SMALLINT UNSIGNED NOT NULL,
  `idx_newed`    SMALLINT UNSIGNED NOT NULL,
  `lessonnum`    TINYINT UNSIGNED NOT NULL,
  `strokecount`  TINYINT UNSIGNED NOT NULL,
  PRIMARY KEY (`ucs_id`),
  UNIQUE KEY `idx_olded` (`idx_olded`),
  UNIQUE KEY `idx_newed` (`idx_newed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------------------------------------------------------
-- learnedkanji
-- ----------------------------------------------------------------------------
-- This table maintains the list of kanji that the user has (re)"learned" by
-- clicking the "learn" button in the Study page. The selection then allows to
-- review just those learned kanji. The kanji are cleared during succesful review
-- or when the user chooses "clear" in the Study page to empty the list.
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS learnedkanji;

CREATE TABLE `learnedkanji` (
  `userid`       MEDIUMINT(4) UNSIGNED NOT NULL,
  `ucs_id`       SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY  (`userid`,`ucs_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------------------------------------------------------
-- reviews
-- ----------------------------------------------------------------------------
--  userid        : from users table
--  ucs_id        : UCS-2 code value (16 bit).
--                  LEFT JOIN kanjis to get Heisig frame numbers.
--  lastreview    : timestamp of last review date for this kanji
--  expiredate    : scheduled date for review of this kanji
--  totalreviews  : total number of reviews for the kanji
--  leitnerbox    : Leitner (flashcard system) box slot number 1-8
--  failurecount  : total number of times answered 'no'
--  successcount  : total number of times answered 'yes'
--  leitnerbox = 1 && totalreviews = 0 : untested cards
--  leitnerbox = 1 && totalreviews > 0 : failed cards
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS reviews;

CREATE TABLE `reviews` (
  `userid`       MEDIUMINT(4) UNSIGNED NOT NULL,
  `ucs_id`       SMALLINT UNSIGNED NOT NULL,
  `created_on`   TIMESTAMP NOT NULL DEFAULT 0,
  `lastreview`   TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expiredate`   DATE NOT NULL DEFAULT '0000-00-00',
  `totalreviews` SMALLINT(4) UNSIGNED NOT NULL,
  `leitnerbox`   TINYINT(1) UNSIGNED NOT NULL,
  `failurecount` SMALLINT(4) UNSIGNED NOT NULL,
  `successcount` SMALLINT(4) UNSIGNED NOT NULL,
  PRIMARY KEY  (`userid`, `ucs_id`),
  KEY `created_on` (`created_on`),
  KEY `lastreview` (`lastreview`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



-- ----------------------------------------------------------------------------
-- sfs_activitylog
-- ----------------------------------------------------------------------------
-- See  data/schemas/incremental/rtk_0006_stopforumspam.sql;

-- ----------------------------------------------------------------------------
-- sfs_blockedips
-- ----------------------------------------------------------------------------
-- See  data/schemas/incremental/rtk_0006_stopforumspam.sql;


-- ----------------------------------------------------------------------------
-- sitenews
-- ----------------------------------------------------------------------------
-- Simplistic news posts displayed on the site's home page.  Should really
-- replace this with a lightweight blog so people can post comments...
-- Note: news posts can be edited in the administration area (nothing fancy),
-- this admin area (the "backend" app) is not included.
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS sitenews;

CREATE TABLE `sitenews` (
  `id`           INT(11) NOT NULL auto_increment,
  `subject`      VARCHAR(64) DEFAULT NULL,
  `text`         TEXT NOT NULL,
  `created_on`   DATE DEFAULT NULL,
  `updated_on`   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY 'main_sort' (`created_on`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------------------------------------------------------
-- stories
-- ----------------------------------------------------------------------------
-- The primary key is useful for update queries (userid, ucs_id constants).
--
--  * Use VARCHAR and not TEXT to avoid on disk temporary tables (ideally, we avoid "Using temporary" altogether)
--
--  * Use a small PRIMARY KEY for stories (sid) for InnoDB, may be useful later for JOINs & Join Decomposition
--
--
--  ABANDONED (not supported on web host)
--  
--  * Add PARTITION by "public" value : this lets us prune all the non-public stories from the Shared Stories
--    queries, and potentially hold it in memory since this partition is less than 100 MB instead of 1 GB

DROP TABLE IF EXISTS stories;

CREATE TABLE `stories` (
  `sid`        int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ucs_id`     smallint(5) unsigned NOT NULL,
  `userid`     int(10) unsigned NOT NULL,

  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `public`     tinyint(1) NOT NULL,
  `text`       varchar(512) NOT NULL,

  PRIMARY KEY (`sid`, `public`),
  UNIQUE KEY `user_stories` (`userid`,`ucs_id`,`public`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ----------------------------------------------------------------------------
-- stories_shared
-- ----------------------------------------------------------------------------
-- NEW SCHEMA  stories_shared
--  * Improve the shared stories index so it can be used for sorting (no "filesort" step)
--  * Used as the main table for Shared Stories queries, JOIN with stories USING sid
--  * Summary table for the stars/reports counts
--  * Restricts the indexes for the main sort, and the "newest stories" sorts to only the public stories

CREATE TABLE `stories_shared` (
  `sid`        int(10) unsigned NOT NULL,
  `ucs_id`     smallint(5) unsigned NOT NULL,
  `userid`     int(10) unsigned NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stars`      smallint(5) unsigned NOT NULL DEFAULT '0',
  `reports`    smallint(5) unsigned NOT NULL DEFAULT '0',

  PRIMARY KEY (`sid`),
  KEY `main_sort` (`ucs_id`,`stars`,`updated_on`),
  KEY `newest_sort` (`ucs_id`,`updated_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ----------------------------------------------------------------------------
-- storiesscores
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS storiesscores;

CREATE TABLE `storiesscores` (
  `ucs_id`       SMALLINT UNSIGNED NOT NULL,
  `authorid`     MEDIUMINT(8) UNSIGNED NOT NULL,
  `stars`        SMALLINT(5) UNSIGNED NOT NULL,
  `kicks`        SMALLINT(5) UNSIGNED NOT NULL,
  PRIMARY KEY  (`ucs_id`,`authorid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------------------------------------------------------
-- storyvotes
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS storyvotes;

CREATE TABLE `storyvotes` (
  `authorid`     int(10) unsigned NOT NULL,
  `ucs_id`       smallint(5) unsigned NOT NULL,
  `userid`       int(10) unsigned NOT NULL DEFAULT '0',
  `vote`         tinyint(1) NOT NULL DEFAULT '0',

  PRIMARY KEY (`userid`,`ucs_id`,`authorid`),
  KEY `count_votes` (`authorid`,`ucs_id`,`vote`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------------------------------------------------------
-- users
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS users;

CREATE TABLE `users` (
  `userid`       int(10) NOT NULL AUTO_INCREMENT,
  `username`     VARCHAR(32) NOT NULL DEFAULT '',
  `password`     VARCHAR(40) NOT NULL DEFAULT '',
  `userlevel`    INT(11) NOT NULL DEFAULT '1',
  `joindate`     DATETIME DEFAULT NULL,
  `lastlogin`    DATETIME DEFAULT NULL,
  `email`        VARCHAR(100) DEFAULT NULL,
  `location`     VARCHAR(32) DEFAULT NULL,
  'regip'        VARCHAR(45) NOT NULL DEFAULT '',
  `timezone`     FLOAT NOT NULL DEFAULT '-6',
  `opt_sequence` tinyint(3) unsigned NOT NULL,
  
  PRIMARY KEY  (`userid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

