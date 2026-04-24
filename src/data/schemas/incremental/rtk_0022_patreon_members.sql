--
-- Date: 2026.04
--
-- Patreon members table updated by patron.php script.
--

DROP TABLE IF EXISTS patreon_members;

CREATE TABLE `patreon_members` (
  `member_id`              CHAR(36)      NOT NULL,
  `full_name`              VARCHAR(255)  NOT NULL DEFAULT '',
  `email`                  VARCHAR(255)  NOT NULL DEFAULT '',
  `patron_status`          VARCHAR(20)   NOT NULL DEFAULT '',
  `lifetime_support_cents` INT UNSIGNED  NOT NULL DEFAULT 0,
  `pledge_start`           DATE          NOT NULL DEFAULT '0000-00-00',
  `hide_pledges`           TINYINT(1)    UNSIGNED NOT NULL DEFAULT 0,
  `updated_at`             TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
