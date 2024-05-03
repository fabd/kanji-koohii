<?php

/**
 * Global helper to retrieve database connection.
 *
 * @throws sfException if connection can not be established (usually SQL is too busy)
 *
 * @return coreDatabaseMySQL
 */
function kk_get_database()
{
  static $db = null;

  // create database connection only when needed
  if (!$db)
  {
    $db = new coreDatabaseMySQL(sfConfig::get('app_db_connection'));
    $db->connect();
  }

  return $db;
}

class koohiiConfiguration extends sfApplicationConfiguration
{
  private $profile_time;

  public function configure()
  {
    $this->profileStart();
  }

  public function initialize()
  {
    // because of sf cache:clear repeating this code??
    if (!defined('CORE_ENVIRONMENT'))
    {
      // FIXME .. refactor to sf
      define('CORE_ENVIRONMENT', $this->getEnvironment());

      define('KK_ENV_DEV', CORE_ENVIRONMENT === 'dev');
      define('KK_ENV_PROD', CORE_ENVIRONMENT === 'prod' || CORE_ENVIRONMENT === 'linode');

      define('KK_ENV_FORK', sfConfig::get('app_fork'));

      // FIXME obsolete, clean up
      define('CJ_MODE', 'rtk');
      define('CJ_HANZI', false);
      // translate between kanji/hanzi sites, FIXME @obsolete
      function _CJ($sid)
      {
        return $sid;
      }
      // Return the string with first character uppercase. FIXME @obsolete
      function _CJ_U($sid)
      {
        return ucfirst(_CJ($sid));
      }

      // autoloading of old Peer classes, ensures self::$db is always set
      require sfConfig::get('sf_lib_dir').'/core/coreAutoload.php';
      coreAutoload::register();
    }

    // events
    $this->dispatcher->connect('flashcards.update', ['rtkUser', 'eventUpdateUserKnownKanji']);
  }

  public function profileStart()
  {
    $this->profile_time = microtime(true);
  }

  public function profileEnd()
  {
    return null !== $this->profile_time ? sprintf('%.0f', (microtime(true) - $this->profile_time) * 1000) : 0;
  }

  public function getAdminInfoFooter()
  {
    $profiler = kk_get_database()->getProfiler();

    $totaltime = (microtime(true) - $this->profile_time);

    if (null === $profiler)
    {
      return $totaltime;
    }

    $phptime = $totaltime - $profiler->getQueryTime();
    $sqltime = $profiler->getQueryTime();

    if ($totaltime > 0)
    {
      $percentphp = number_format((($phptime / $totaltime) * 100), 2);
      $percentsql = number_format((($sqltime / $totaltime) * 100), 2);
    }
    else
    {
      // if we've got a super fast script...
      $percentphp = 0;
      $percentsql = 0;
    }

    $text = 'Generated in '.$profiler->format_time_duration($totaltime)." ({$percentphp}% PHP / {$percentsql}% MySQL)";

    return <<<END
      <div style="background:#222;display:flex;justify-content:right;">
        <div style="background:#fff;padding:5px 10px;font:15px/1em Arial;">
          {$text}
        </div>
      </div>
      END;
  }
}
