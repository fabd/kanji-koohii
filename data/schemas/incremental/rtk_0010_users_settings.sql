--
-- Date: 2015.04.xx
--
-- Create the users_settings table, which stores application preferences (as opposed
-- to user profile).
--

CREATE TABLE `users_settings` (
  `userid`       MEDIUMINT(4) UNSIGNED NOT NULL,
  `created_on`   TIMESTAMP NOT NULL DEFAULT 0,
  `updated_on`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  `no_shuffle`   TINYINT(1) UNSIGNED NOT NULL,

  PRIMARY KEY  (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Date 2015.05.xx
--
-- On/Kun Readings
--

ALTER TABLE users_settings ADD show_onkun TINYINT UNSIGNED NOT NULL;

