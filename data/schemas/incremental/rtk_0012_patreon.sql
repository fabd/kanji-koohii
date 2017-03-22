#
# Date: 2017.03
#
#  PATREON
#  
#    Updated via CRON JOB
#    $ update_pledges --env prod
#    
#    pa_active            ...        1
#
#    pa_id                ...        ['data'][x]['id']  (not needed but may help debug)
#    pa_full_name         ...        ['included'][x]['attributes']['full_name']
#    pa_amount_cents      ...        ['data'][x]['attributes']['amount_cents']
#    pa_created_at        ...        ['data'][x]['attributes']['created_at']  (pledge age)
#    
#    userid               ...        Koohii user id
#    
#    
#  UNNECESSARY
#    
#    pa_access_token      received from patreon API\OAuth(client_id, client_secret)=>get_tokens()
#    pa_refresh_token     ...
#    pa_expires_in        ... in seconds, Patreon API returns 60*60*24*31 = 2678400 = 31 days
#
#
#  In case we need to create pledges (testing?)
#  
#    $ edituser -env prod -u foobar --pledge <months>
#  
#    pa_id            <==  0  (means: not a Patreon user)
#    pa_full_name     <==  username
#    pa_amount_cents  <==  UsersPatreon::AMOUNT_CENTS_BACKER
#    pa_created_at    <=== NOW()
#
#
#

CREATE TABLE `patreon` (

  `userid`          int(10) unsigned NOT NULL DEFAULT '0',

  `is_active`       tinyint unsigned NOT NULL DEFAULT '0',
  `has_perks`       tinyint unsigned NOT NULL DEFAULT '0',

  `pa_id`           int(10) unsigned not null,
  `pa_amount_cents` int(10) unsigned not null default '0',
  `pa_created_at`   DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pa_full_name`    varchar(100) not null default '',

  `updated_on`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`pa_id`),
  KEY `users_join` (`userid`),
  KEY `patrons_list` (`is_active`, `pa_created_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



-- ALTER TABLE patreon ADD COLUMN `is_active` TINYINT NOT NULL DEFAULT '0';
-- ALTER TABLE patreon ADD KEY `created_at` (`pa_created_at`);
-- ALTER TABLE patreon ADD KEY `amount_cents` (`pa_amount_cents`);
