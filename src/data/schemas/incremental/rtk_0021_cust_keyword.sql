--
-- Date: 2026.03
--
-- Increase Custom Keyword length.
--

LOCK TABLES custkeywords WRITE;

--
-- Check for custom keywords which are identical to the default keyword.
-- 
-- SELECT COUNT(*) FROM custkeywords ck LEFT JOIN kanjis k USING (ucs_id) WHERE ck.keyword = k.keyword;
-- DELETE ck FROM custkeywords ck LEFT JOIN kanjis k USING (ucs_id) WHERE ck.keyword = k.keyword;

--
-- Update table
--
ALTER TABLE `custkeywords` MODIFY `keyword` VARCHAR(40) NOT NULL DEFAULT '';

UNLOCK TABLES;
