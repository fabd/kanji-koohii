#
# Date: 2017.07.31
#
#   Log registration IP for new accounts. Helps with spam/bots;
#   
#   And yes, we could store packed addresses to be efficient (ie. inet_ntop()),
#   but this will do for now, and I want it to be easily readable for now.
#

ALTER TABLE users ADD COLUMN `regip` VARCHAR(45) NOT NULL DEFAULT '' AFTER location;


# Date: 2017.08.01
#
#   Add a key for query to check max registrations within timespan.
#

ALTER TABLE users ADD KEY `regip` (`regip`);
