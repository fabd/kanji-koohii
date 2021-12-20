#
# Date: 2021.12
#
#  REVIEW SUMMARY REFACTOR
#  
#    Cf. PR feature/flashcard-review--again-rating
#  
#    We don't store pass/fail/timestamp in the db anymore.
#

LOCK TABLES active_members WRITE;

ALTER TABLE active_members DROP lastrs_start;
ALTER TABLE active_members DROP lastrs_pass;
ALTER TABLE active_members DROP lastrs_fail;

UNLOCK TABLES;

