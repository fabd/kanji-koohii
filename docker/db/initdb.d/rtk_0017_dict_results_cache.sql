--
-- Date: 2018.10
--
-- This table stores JSON results for dictionary lookups to speed up queries.
--

-- ----------------------------------------------------------------------------
-- cache_dict_lookup
-- ----------------------------------------------------------------------------
--  ucs_id        : UCS-2 code value (16 bit).
--  num_entries   : total of dict entries, 0 means "no priority entries" (not top 16,000 JMDICT words)
--  json          : array of DictEntry results (id, compound, reading, glossary, priority)
--                : (6150 char_length just fits the longest JSON with 50 results / kanji)
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS cache_dict_lookup;

CREATE TABLE `cache_dict_lookup` (

  `ucs_id`       SMALLINT UNSIGNED NOT NULL,
  `num_entries`  SMALLINT UNSIGNED NOT NULL DEFAULT '0',

  `json`         VARCHAR(6150) NOT NULL DEFAULT '',

  PRIMARY KEY  (`ucs_id`)

) ENGINE=MyISAM DEFAULT CHARSET=utf8;

