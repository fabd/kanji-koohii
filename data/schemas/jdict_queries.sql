-- ----------------------------------------------------------------------------
--
-- This file documents various kind of queries used by the website.
--
--
-- REFERENCES
--
--   For bitmasks in jdict table see:
--
--    data/scripts/dict/parse_dict.pl
--    apps/koohii/lib/rtkLabs.php
-- 
-- ----------------------------------------------------------------------------


-- ----------------------------------------------------------------------------
-- QUERY - Lookup example words for a given kanji
-- ----------------------------------------------------------------------------
--
--   pri (bitmask)
--
--     0xCA  =>  ichi1, news1, spec1, gai1

-- 24535 志 "intention"
select jdict.pri,compound,reading,glossary,misc,verb
  FROM jdict
  JOIN dictsplit USING(dictid)
  WHERE kanji=24535 AND jdict.pri & 0xCA
  ORDER BY pri DESC;


-- ----------------------------------------------------------------------------
-- QUERY - Order by length of compound
-- ----------------------------------------------------------------------------

-- 38283 開 "open"
select jd.pri,compound,reading,glossary,misc,verb
  FROM jdict jd
  JOIN dictsplit ds ON jd.dictid = ds.dictid
  WHERE ds.kanji = 38283 AND jd.pri & 0xCA
  ORDER BY ds.numkanji;


-- ----------------------------------------------------------------------------
-- Vocab Shuffle > "Discover words based on RTK index"
-- ----------------------------------------------------------------------------

-- Find JMDICT entries which use a kanji of RTK index <= 46

SELECT compound, reading, glossary, framenum, jd.pri
  FROM jdict AS jd
  JOIN dictlevels USING(dictid)
  WHERE jd.pri & 0xCA AND framenum <= 46
  ORDER BY framenum;


-- ----------------------------------------------------------------------------
-- Vocab Shuffle > "Discover words made only of learned kanji"
-- ----------------------------------------------------------------------------

-- This query shows the purpose of "numkanji".
--
-- We are trying to solve "find all possible words with a given, possibly
-- long (2000+) set of kanji".
--
-- We can do fancy queries in MySQL but we need something FAST, and preferably
-- without temporary tables. We want to use keys, and lookup as few rows as 
-- possible.

-- In this solution, we added a "numkanji" field to the dictsplit table, which
-- indicates the total length of the compound (dictid) which this dictsplit
-- entry corresponds to. Now when we cross the user's set of known kanji with
-- dictsplit, we can use GROUP BY and COUNT(*), and count the dictsplit parts
-- per unique dictid. If the count of parts === numkanji, then we know that
-- the user's kanji set totally covers this particular JMDICT entry.


SELECT dictid, compound, reading, ds.numkanji, ds.pri, COUNT(*) AS c
  FROM jdict
  JOIN dictsplit AS ds USING (dictid)
  JOIN reviews AS fc ON (ds.kanji = fc.ucs_id AND fc.userid = 1 AND fc.leitnerbox >= 4)
  GROUP BY dictid
  HAVING (c = numkanji AND pri & 0xCA)
  ORDER BY rand();


-- ----------------------------------------------------------------------------
-- BUGS ?
-- ----------------------------------------------------------------------------

-- numkanji shouldn't be zero
SELECT * FROM dictsplit ds WHERE dictid = 1582820;

SELECT * FROM dictsplit ds WHERE kanji = 21475;
