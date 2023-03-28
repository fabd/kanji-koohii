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
