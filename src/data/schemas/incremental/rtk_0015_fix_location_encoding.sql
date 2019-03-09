--
-- Date: 2018.08
--
-- Fix encoding of "location" in user profile to allow for non-english characters.
--  eg. Kraków  México
--

LOCK TABLES users WRITE;

ALTER TABLE users MODIFY `location` VARCHAR(32) CHARACTER SET utf8 NOT NULL DEFAULT '';

-- misc / remove DEFAULT NULL
ALTER TABLE users MODIFY `email` VARCHAR(100) NOT NULL DEFAULT '';

UNLOCK TABLES;