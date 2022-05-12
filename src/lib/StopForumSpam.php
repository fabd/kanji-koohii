<?php
/**
 * Vérification de l'adresse IP et email avec le service StopForumSpam.
 *
 * StopForumSpam API:
 * http://stopforumspam.com/usage
 * 
 * MySQL updates required (vim 'gf') see:
 * ./data/schemas/incremental/rtk_0006_stopforumspam.sql
 *
 */

class StopForumSpam
{
  // name of table used for logging registration attempts
  const SFS_BLOCKEDIPS  = 'sfs_blockedips';

  // name of table keeping a log of misc events and errors
  const SFS_ACTIVITYLOG = 'sfs_activitylog';

  // how many days back to keep in the logs
  const SFS_BLOCKEDIPS_TRIM  = 7;
  const SFS_ACTIVITYLOG_TRIM = 5;

  // checkRegistration() return value: the IP is listed as a spammer
  const SFS_CR_FAILED   = -1;

  // checkRegistration() return value: a connection timeout occured while connecting to third party
  const SFS_CR_TIMEOUT  = -2;

  public function __construct()
  {
    $this->db = sfProjectConfiguration::getActive()->getDatabase();
  }

  public static function getInstance(): self
  {
    static $instance = null;
    $instance ??= new self();

    return $instance;
  }

  /**
   *
   * @return string   Readable IPv4 or IPv6 address
   */
  public static function getRemoteAddress()
  {
    $pathArray = sfContext::getInstance()->getRequest()->getPathInfoArray();
    $remote_addr = $pathArray['REMOTE_ADDR'];

    $ip = strtolower($remote_addr);

    // check forwarded IP ?
    /*
    if (true === $ip_forwarded_check)
    {
      $addresses = array();

      if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
      {
        $addresses = explode(',', strtolower($_SERVER['HTTP_X_FORWARDED_FOR']));
      }
      elseif(isset($_SERVER['HTTP_X_REAL_IP']))
      {
        $addresses = explode(',', strtolower($_SERVER['HTTP_X_REAL_IP']));
      }

      if(is_array($addresses))
      {
        foreach($addresses as $val)
        {
          $val = trim($val);
          // Validate IP address and exclude private addresses
          if (my_inet_ntop(my_inet_pton($val)) == $val && !preg_match("#^(10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.|fe80:|fe[c-f][0-f]:|f[c-d][0-f]{2}:)#", $val))
          {
            $ip = $val;
            break;
          }
        }
      }
    }*/

    if (!$ip)
    {
      if (isset($_SERVER['HTTP_CLIENT_IP']))
      {
        $ip = strtolower($_SERVER['HTTP_CLIENT_IP']);
      }
    }

    return $ip;
  }

  /**
   * Checks the email address and IP with third party spammer database service.
   *
   * @param  string  $username   Username for logging purposes
   * @param  string  $email      Email address is checked with third party
   * @param  string  $answer     The answer to the question for logging purposes
   *
   * @return int    Returns 0 if the account seems legit, otherwise see self::SFS_CR_xxx
   */
  public function checkRegistration($username, $email, $answer)
  {
    // clean the log here
    $this->trimLogs();

    // timeout for fsock/curl connection to StopForumSpam, in seconds
    $timeout = 5;

    $ip  = self::getRemoteAddress();
    $now = time();

    // &unix for unix time
    $url = 'http://www.stopforumspam.com/api?ip='.$ip.'&email='.urlencode($email).'&f=serial&unix';
    $r = GetRemoteFile::fetchUrl($url, $timeout);

    if (null === $r || null === ($r = unserialize($r['content'])))
    {
      // let's log so that we know if something's not working as expected
      $this->logActivity($ip, 'Timeout/error while checking IP with StopForumSpam.');
      return self::SFS_CR_TIMEOUT;
    }

//echo '<pre>'.print_r($r, true).'</pre>';exit;

    if ($r['success'] == 1 && array_key_exists('ip', $r))
    {
      if ($r['ip']['appears'] > 0)
      {
        $lastseen_days = (time() - intval($r['ip']['lastseen'])) / (24 * 60 * 60);

        // not as lenient as used to be as spambots now get through the question
        if ($lastseen_days < 7 || $r['ip']['frequency'] > 10)
        {
          // reported more than once, OR reported just once but recently enough

          $this->logAttempt($ip, $username, $email, $now, $r['ip']['frequency']);

          $this->logActivity($ip, 'SFS: blocked IP\'s answer was: "'.$answer.'"');
          
          return self::SFS_CR_FAILED;
        }
        else
        {
          // reported once, AND not recently: let it pass through other checks
          $this->logActivity($ip, 'NOTE: User with '.$r['ip']['frequency'].' report(s) passing through.');
        }
      }

      /* Dec 2015: WTF lastseen = time() actuel c'est buggé!
      if ($r['email']['appears'] > 0)
      {
        $lastseen_days = (time() - intval($r['email']['lastseen'])) / (24 * 60 * 60);

        if ($lastseen_days < 30)
        {
          // TODO logger le flag IP ou EMAIL, on log un message en attendant..
          $this->logActivity($ip, 'SFS: blocked email "'.$email.'" with recent activity.');
          
          return self::SFS_CR_FAILED;
        }
        else
        {
          // reported once, AND not recently: let it pass through other checks
          $this->logActivity($ip, 'NOTE: Email "'.$email.'" with '.$r['email']['frequency'].' report(s) passing through.');
        }
      }
      */
    }
  }

  // keep a log of misc. actions or errors to help improve or find issues
  public function logActivity($ip, $description)
  {
    $logtime = time();

    $this->db->insert(self::SFS_ACTIVITYLOG, [
      'ip'        => $ip,
      'logtime'   => time(),
      'logdesc'   => $description
    ]);
  }

  // this now logs only registration attempts with a valid answer, but listed on StopForumSpam
  public function logAttempt($ip, $username, $email, $bot_visit, $frequency)
  {
    $logtime = time();

    $this->db->insert(self::SFS_BLOCKEDIPS, [
      'ip'        => $ip,
      'username'  => $username,
      'email'     => $email,
      'bot_visit' => $bot_visit,
      'frequency' => $frequency
    ]);
  }

  public function getSelectForActivityLog()
  {
    return $this->db->select()->from(self::SFS_ACTIVITYLOG);
  }
  
  public function getSelectForBlockedIPs()
  {
    return $this->db->select()->from(self::SFS_BLOCKEDIPS);
  }

  // clean the log by deleting all entries older than N days.
  public function trimLogs()
  {
    $mintime = time() - (self::SFS_BLOCKEDIPS_TRIM * 24 * 60 * 60);
    $this->db->delete(self::SFS_BLOCKEDIPS, 'bot_visit < ?', $mintime);
    
    $mintime = time() - (self::SFS_ACTIVITYLOG_TRIM * 24 * 60 * 60);
    $this->db->delete(self::SFS_ACTIVITYLOG, 'logtime < ?', $mintime);
  }
}
