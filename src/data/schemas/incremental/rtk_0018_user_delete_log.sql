#
# Date: 2021.01.19
#
#  Table to log user account deletions.
#  Keeps track of released usernames, and userids in case of issues.
#  
#  NOTES
#  
#    - Only last N days will be kept so the table will not become very long
#      and the user's info is eventually completely removed.
#
# @See  ./lib/UserDeleteLog.php
#

-- ----------------------------------------------------------------------------
-- log_user_delete
-- ----------------------------------------------------------------------------
-- We *don't* need unique keys here on userid or username : it's just a log,
-- less trouble with INSERTs while testing.
--
--   created_on         time of account deletion, UTC
--   joindate           account's creation time, UTC (from users.joindate)
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS log_user_delete;

CREATE TABLE `log_user_delete` (
  `created_on`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userid`       MEDIUMINT(4) UNSIGNED NOT NULL,
  `username`     varchar(200) NOT NULL DEFAULT '',
  `joindate`     TIMESTAMP NOT NULL DEFAULT '0',
  `logdesc`      VARCHAR(200) NOT NULL DEFAULT '',
  KEY `created_on` (`created_on`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
