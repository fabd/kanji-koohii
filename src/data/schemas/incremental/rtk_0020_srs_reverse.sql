--
-- Date: 2022.10.26
--
-- Add reverse mode (ie. "kanji to keyword") to SRS Settings.
--
--
--  srs_reverse    0 = keyword > kanji, 1 = kanji > keyword
--

ALTER TABLE users_settings ADD srs_reverse  TINYINT  UNSIGNED NOT NULL DEFAULT 0;
