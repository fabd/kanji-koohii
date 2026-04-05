-- ----------------------------------------------------------------------------
--
-- KANJI KOOHII - Database Schema
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
-- This table keeps track of recent visitors with unique ips, who are
-- not signed in. Currently unused.
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
-- This table stores aggregated activity data for a use, which can be used
-- in place of more complex queries.
--
-- On create/update not all columns may be set, so each column has to have
-- a default value.
-- 
-- The main use for this table for now is to allow to revisit the flashcard
-- review summary without a POST request. So long as another review was not
-- started, the last review timestamp can be used to find flashcards updated
-- in the last review.
--
-- This table is also used to display a list of users who have review in the
-- last N days, each with their total flashcard count.
-- 
--  userid
--  fc_count      Total flashcards for this user
--  last_review   Timestamp of beginning of the last flashcard review
--
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS active_members;

CREATE TABLE `active_members` (
  `userid`       MEDIUMINT(4) UNSIGNED NOT NULL,
  `fc_count`     SMALLINT NOT NULL DEFAULT 0,
  `last_review`  DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (`userid`),
  KEY `last_review` (`last_review`),
  KEY `fc_count` (`fc_count`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------------------------------------------------------
-- active_users
-- ----------------------------------------------------------------------------
-- This table keeps track of recently signed in users. Currently unused.
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
-- A custom keyword is a keyword edited by the user, which replaces the default
-- keyword (the default keywords come from the `kanjis` table).
-- 
-- Typically, the keyword displayed to a user in the app is obtained in a query
-- by using the COALESCE function.
-- eg. `COALESCE(custkeywords.keyword, kanjis.keyword)`.
--
--   ucs_id    UCS-2 code of the kanji for which the custom keyword applies
--
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS custkeywords;

CREATE TABLE `custkeywords` (
  `userid`       MEDIUMINT(4) UNSIGNED NOT NULL,
  `ucs_id`       SMALLINT(5)  UNSIGNED NOT NULL,
  `keyword`      VARCHAR(40) NOT NULL DEFAULT '',
  `created_on`   TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_on`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (`userid`, `ucs_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------------------------------------------------------
-- kanjis
-- ----------------------------------------------------------------------------
-- This table is only read from. It contains the data for all the kanjis in the
-- CJK Unified Ideographs range. The data is derived from KANJIDIC2 xml file.
-- The kanjis_table.php script parses KANJIDIC2 and outputs a csv file with just
-- the data we need. The csv file is imported in this table with a LOAD DATA
-- statement.
-- 
--   ucs_id     UCS-2 code for the kanji, uniquely identifying a character
--   keyword    The default english keyword associated with this kanji
--   onyomi     one of the main on-yomi readings (in katakana, eg. `ショウ`)
--   idx_olded  RTK index, from old edition of RTK books (up to 5th edition)
--   idx_newed  RTK index from the most recent edition of RTK (aka "6th edition")
--   strokecount  The stroke count for this kanji (displayed on Study pages)
--
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS kanjis;

CREATE TABLE `kanjis` (
  `ucs_id`       SMALLINT UNSIGNED NOT NULL,
  `keyword`      CHAR(40) NOT NULL DEFAULT '',
  `kanji`        CHAR(1) NOT NULL DEFAULT '',
  `onyomi`       VARCHAR(50) NOT NULL DEFAULT '',
  `idx_olded`    SMALLINT UNSIGNED NOT NULL,
  `idx_newed`    SMALLINT UNSIGNED NOT NULL,
  `strokecount`  TINYINT UNSIGNED NOT NULL,
  PRIMARY KEY (`ucs_id`),
  UNIQUE KEY `idx_olded` (`idx_olded`),
  UNIQUE KEY `idx_newed` (`idx_newed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------------------------------------------------------
-- learnedkanji
-- ----------------------------------------------------------------------------
-- This table keeps a list of kanji that the user has "learned" by
-- clicking the "learn" button in the Study page, as part of the "Restudy" flow.
-- The user is then able to review just this selection of kanji. Whenever user
-- reviews a kanji, it is cleared from the learned list.
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
-- This table stores the flashcard data for the user's kanji flashcards. User
-- can create a flashcard for any kanji in the CJK Unified Ideographs unicode
-- range. Each flashcard has a timestamp of last review, as well as spaced
-- repetition scheduling data.
--
-- Note!
--   - Forgotten cards (red color) have leitnerbox 1 and totalreviews > 0
--   - New cards (blue color) have leitnerbox 1  and totalreviews === 0
--   - New cards move to leitnerbox 2 after 1st review, leitnerbox 3 after
--     2nd succesful review, and so on.
--
--  userid
--  ucs_id        : UCS-2 code for the kanji
--  lastreview    : timestamp of last time this card was reviewed
--  expiredate    : scheduled date for next review of this kanji
--  totalreviews  : total number of reviews
--  leitnerbox    : Leitner box
--  failurecount  : total number of times card was rated No (forgotten)
--  successcount  : total number of times card was rated Hard/Yes/Easy (remembered)
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS reviews;

CREATE TABLE `reviews` (
  `userid`       MEDIUMINT(4) UNSIGNED NOT NULL,
  `ucs_id`       SMALLINT UNSIGNED NOT NULL,
  `created_on`   TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
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
--   Keeps a log related to failed registration attempts, to diagnose issues
--   with registration, or sources of spam. Displayed in the admin dashboard.
-- ----------------------------------------------------------------------------
-- See  data/schemas/incremental/__rtk_0006_stopforumspam.sql;

-- ----------------------------------------------------------------------------
-- sfs_blockedips
--   This table logs registration attempts where StopForumSpam service flagged
--   the email or IP address as spam. Displayed in the admin dashboard.
-- ----------------------------------------------------------------------------
-- See  data/schemas/incremental/__rtk_0006_stopforumspam.sql;

-- ----------------------------------------------------------------------------
-- sitenews
-- ----------------------------------------------------------------------------
-- The news blog displayed on the home page. Only admin can edit these posts.
-- 
--   subject     Title for this news post
--   text        Contents of the post in Markdown format
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS sitenews;

CREATE TABLE `sitenews` (
  `id`           INT(11) NOT NULL auto_increment,
  `subject`      VARCHAR(64) DEFAULT NULL,
  `text`         TEXT NOT NULL,
  `created_on`   DATE DEFAULT NULL,
  `updated_on`   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `main_sort` (`created_on`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------------------------------------------------------
-- stories
-- ----------------------------------------------------------------------------
-- This is one of the largest tables in the database, typically 8 million+ rows.
-- It holds stories edited
-- by all users, both private and public stories. Stories can be individually
-- deleted only if the user edits a story with empty text.
--
-- Using VARCHAR to store the user's story should be more performant than TEXT
-- when content is < 768 bytes (stored inline in the row).
--
-- Storage and index performance is paramount with this table.
--
--   sid      Unique id for the story (across all users)
--   ucs_id   UCS-2 code of the kanji associated with this story
--   public   1 = story is shared (display to other users), 0 = private
--   text     Story with light custom formatting (eg. `*italic text*`)
--
-- ----------------------------------------------------------------------------


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
-- Used as the main table for displaying Shared Stories on the Study page, for
-- any given kanji. JOIN with stories USING sid to get the corresponding story
-- text.
--
-- This table also aggregates a count of stars and reports, to simplify the
-- Shared Stories query.
-- ----------------------------------------------------------------------------

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
-- storyvotes
-- ----------------------------------------------------------------------------
-- This table stores all unique upvotes and reports from users issued in the
-- Shared Stories list. When user clicks the star button, a record is created
-- with vote === 1. When user reports a story, a record is created with
-- vote === 2. A story can not have a star and a report at the same time.
-- 
-- The stars and reports from all users for any given story is aggregated
-- in the stories_shared table (stars, reports).
--
-- A story here is uniquely identified by (authorid, ucs_id). Perhaps a better
-- design would have been to use stories.sid instead.
-- 
--   authorid   User id of the story being upvoted or reported
--   userid     User id of the user voting
--   vote       1 = starred (counts as a +1 vote)   2 = report (counts as -1 vote)
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
-- The users table. Currently user level (role) is very simple: there is only
-- one admin (level 9), all other users are level 1. There is currently no
-- intermediate levels (eg. moderator).
--
--   password     Encrypted password
--   userlevel    9 = admin , 1 = all other users
--   location     Optional, from the "Where do you live?" question on signup
--                eg. "Spain", or "Tokyo, Japan"
--   regip        IP at registration time, IPv4 dotted-decimal notation
--                eg. `172.18.0.1`
--   timezone     The user's timezone (editable in Account Settings), is used
--                to determine when new flashcards have become due for review
--                (midnight time adjusted by timezone).
--   opt_sequence RTK index edition (0 = old edition, 1 = new edition) determines
--                whether to use idx_olded or idx_newed in the kanjis table.
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS users;

CREATE TABLE `users` (
  `userid`       int(10) NOT NULL AUTO_INCREMENT,
  `username`     VARCHAR(32) NOT NULL DEFAULT '',
  `password`     VARCHAR(40) NOT NULL DEFAULT '',
  `userlevel`    INT(11) NOT NULL DEFAULT '1',
  `joindate`     DATETIME DEFAULT NULL,
  `lastlogin`    DATETIME DEFAULT NULL,
  `email`        VARCHAR(100) NOT NULL DEFAULT '',
  `location`     VARCHAR(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `regip`        VARCHAR(45) NOT NULL DEFAULT '',
  `timezone`     FLOAT NOT NULL DEFAULT '-6',
  `opt_sequence` tinyint(3) unsigned NOT NULL DEFAULT 0,
  
  PRIMARY KEY  (`userid`),
  UNIQUE KEY `username` (`username`),
  KEY `regip` (`regip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------------------------------------------------------
-- users_settings
-- ----------------------------------------------------------------------------
-- User's settings the user can change in Account Settings. If there is no record
-- the app uses default values.
--
--  no_shuffle     1 = do not shuffle new cards (blue pile)
--  srs_max_box    Number of Leitner boxes (excluding the first one with forgotten
--                 and new cards). 5 to 10, default is 7
--  srs_mult       SRS review interval multiplier, float x.xx stored as integer
--                 (eg. 205 = 2.05). Default is 205 (2.05)
--  srs_hard_box   Maximum box for cards marked 'Hard' (0 to 9)
--  srs_reverse    0 = keyword to kanji, 1 = kanji to keyword
-- ----------------------------------------------------------------------------

CREATE TABLE `users_settings` (
  `userid`       MEDIUMINT(4) UNSIGNED NOT NULL,
  `created_on`   TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_on`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  `no_shuffle`   TINYINT(1) UNSIGNED NOT NULL,
 
  `srs_max_box`  TINYINT UNSIGNED NOT NULL,
  `srs_mult`     SMALLINT UNSIGNED NOT NULL,
  `srs_hard_box` TINYINT UNSIGNED NOT NULL,
  `srs_reverse`  TINYINT UNSIGNED NOT NULL DEFAULT 0,

  PRIMARY KEY  (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
