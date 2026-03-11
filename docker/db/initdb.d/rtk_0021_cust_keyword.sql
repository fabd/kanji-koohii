--
-- Date: 2026.03
--
-- Increase Custom Keyword length.
--

LOCK TABLES custkeywords WRITE;

ALTER TABLE `custkeywords` MODIFY `keyword` VARCHAR(40) NOT NULL DEFAULT '';

UNLOCK TABLES;
