#
# Date: 2016.08
#
#  High Performance SQL updates (hopefully).
#
#    UPDATE SCHEMA  stories
#
#    * Convert TEXT to VARCHAR to avoid on disk temporary tables (ideally, we will get rid of "Using temporary")
#
#    * Use a small PRIMARY KEY for stories (sid) for InnoDB, may be useful later for JOINs & Join Decomposition
#
#    * Improve the shared stories index so it can be used for sorting (no "filesort" step)
#
#    * Add PARTITION by "public" value : this lets us prune all the non-public stories from the Shared Stories
#      queries, and potentially hold it in memory since this partition is less than 100 MB instead of 1 GB
#
#
#    NEW SCHEMA  stories_shared
#
#    * Used as the main table for Shared Stories queries, JOIN with stories USING sid
#
#    * Summary table for the stars/reports counts
#
#    * Restricts the indexes for the main sort, and the "newest stories" sorts to only the public stories
#
#
#  Analyse:
# 
#    Selectivity : only 6% of stories are public!
#    
#    mysql> SELECT public, COUNT(*) FROM stories GROUP BY public;
#
#    +--------+----------+
#    | public | COUNT(*) |
#    +--------+----------+
#    |      0 |  5835177 |   94 %
#    |      1 |   361405 |    6 %     <= highly selective
#    +--------+----------+
#    
#    An index that starts with `public` is highly selective. We can ensure that such an index is usable anyway
#    by adding AND public IN(0, 1) to the WHERE clause.
#
#
#  Resources:
#
#    Converting Tables from MyISAM to InnoDB
#    http://dev.mysql.com/doc/refman/5.7/en/converting-tables-to-innodb.html
#
#    "BLOB/TEXT columns are not supported with MEMORY storage engine so must use on disk MyISAM temporary table."
#    https://www.percona.com/blog/2007/08/16/how-much-overhead-is-caused-by-on-disk-temporary-tables/
#
#   
#    "Declare the PRIMARY KEY clause in the original CREATE TABLE statement, rather than adding it later through an ALTER TABLE statement."


#  Keep copy of the old table in case we want to revert the live site
#
ALTER TABLE stories RENAME bkp_stories;
ALTER TABLE storiesscores RENAME bkp_storiesscores;

#  Crèer une nouvelle table en InnoDB
#
CREATE TABLE `stories` (
  `sid`        int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ucs_id`     smallint(5) unsigned NOT NULL,
  `userid`     int(10) unsigned NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `public`     tinyint(1) NOT NULL,
  `text`       varchar(512) NOT NULL,
  PRIMARY KEY (`sid`, `public`),
  UNIQUE KEY `user_stories` (`userid`,`ucs_id`,`public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#  LIVE  The server uses --skip-partition  !!!
#
#  PARTITION BY LIST(public) (
#    PARTITION p_pub   VALUES IN (1),
#    PARTITION p_priv  VALUES IN (0)
#);


#  Insérer toutes les rows dans la nouvelle table  (3 min pour 4 million rows)
#
INSERT INTO stories (ucs_id, userid, updated_on, public, text)
  SELECT ucs_id, userid, updated_on, public, text
  FROM bkp_stories;

#  Move the sum of votes and reports into stories
# 
-- UPDATE stories st JOIN storiesscores ss ON (st.ucs_id = ss.ucs_id AND st.userid = ss.authorid) SET st.stars = ss.stars, st.reports = ss.kicks;


-- LOCK TABLES stories WRITE;

--
--  INDEX
--
--  Shared Stories list, main select.
--  "public" has a very high selectivity (only 6-7% of millions of stories are public)
-- 
-- ALTER TABLE stories ADD KEY sort_shared_stories (public, ucs_id, stars, updated_on);

--
--  INDEX
--
--  userid is much more selective because common kanji can have thousands of stories
--     but a user typically has less than 2000 stories
-- 
-- ALTER TABLE stories ADD UNIQUE KEY `user_stories` (`userid`, `ucs_id`);

-- UNLOCK TABLES;


#
#  NEW TABLE
#

CREATE TABLE `stories_shared` (
  `sid`        int(10) unsigned NOT NULL,
  `ucs_id`     smallint(5) unsigned NOT NULL,
  `userid`     int(10) unsigned NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stars`      smallint(5) unsigned NOT NULL DEFAULT 0,
  `reports`    smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO stories_shared (sid, ucs_id, userid, updated_on, stars, reports)
  SELECT s.sid, s.ucs_id, s.userid, s.updated_on, COALESCE(ss.stars, 0), COALESCE(ss.kicks, 0)
  FROM stories s
  LEFT JOIN bkp_storiesscores ss ON (s.ucs_id = ss.ucs_id AND s.userid = ss.authorid)
  WHERE s.public = 1;

ALTER TABLE stories_shared ADD KEY main_sort (ucs_id, stars, updated_on);

ALTER TABLE stories_shared ADD KEY newest_sort (ucs_id, updated_on);

#
#  UPDATE TABLE
#

ALTER TABLE storyvotes
  MODIFY COLUMN `authorid` int UNSIGNED NOT NULL,
  MODIFY COLUMN `userid` int UNSIGNED NOT NULL,
  ADD KEY count_votes (authorid, ucs_id, vote);

