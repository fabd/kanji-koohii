--
-- Date: 2021.01.19
--
-- Tables to log user account deletions.
-- Keeps track of released usernames, and userids in case of
-- issues with the database.
--
-- @See  ./lib/UserDeleteLog.php
--
-- mysql> SOURCE data/schemas/incremental/rtk_0018_...
--

-- ----------------------------------------------------------------------------
-- log_user_delete
-- ----------------------------------------------------------------------------
-- We *don't* need unique keys here on userid or username : it's just a log,
-- less trouble with repeated requests & while testing.
-- Also, only last N days will be kept so the table will not become very long.
--
--   joindate           account's creation time, UTC
--   logtime            time of account deletion, UTC
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS log_user_delete;

CREATE TABLE `log_user_delete` (
  `userid`       MEDIUMINT(4) UNSIGNED NOT NULL,
  `username`     varchar(200) NOT NULL DEFAULT '',
  `joindate`     int(10) unsigned NOT NULL DEFAULT '0',
  `logtime`      int(10) unsigned NOT NULL DEFAULT '0',
  `logdesc`      VARCHAR(200) NOT NULL DEFAULT '',
  KEY `logtime` (`logtime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
