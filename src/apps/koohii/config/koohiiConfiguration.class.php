<?php

// Stub that returns our custom coreDatabase ORM
function kk_get_database(): coreDatabaseMySQL
{
  static $db = null;

  if (!$db) {
    $db = new coreDatabaseMySQL(sfConfig::get('app_db_connection'));
    $db->connect();
  }

  return $db;
}

// Stubs to fix PHPStan errors due to lack of return type from Context in Sf1.4
function kk_get_user(): ?rtkUser
{
  return sfContext::getInstance()->getUser();
}

function kk_get_response(): coreWebResponse
{
  return sfContext::getInstance()->getResponse();
}

function koohii_onload_slot()
{
  $name        = 'koohii_onload_js';
  $prevContent = get_slot($name);
  slot($name);
  echo $prevContent;
  // echo "console.log('koohii_onload_slot()')\n";
}

function koohii_onload_slots_out()
{
  if ($s = get_slot('koohii_onload_js')) {
    echo "<script>\n",
    '/* Koohii onload slot */ ',
    "window.addEventListener('DOMContentLoaded',function(){\n", $s, "});</script>\n";
  }
}

define('KK_GLOBALS', 'kk.globals');

/**
 * Helper to "hydrate" template with data for the frontend.
 *
 * Use `kk_globals_get()` in Javascript (cf. globals.d.ts)
 *
 * Conveniently, this hydration happens BEFORE defered modules
 * from Vite build are run, since defered modules happen after
 * the document is parsed, and \<script>'s are part of the document.
 *
 * @param array<string, mixed>|string $key   key name (convention ALL_UPPERCASE), or array of key => values
 * @param mixed                       $value (if single key) any value that parses to JSON (string, boolean, null, etc)
 */
function kk_globals_put(array|string $key, mixed $value = null)
{
  $kk_globals = sfConfig::get(KK_GLOBALS);
  if (null === $kk_globals) {
    $kk_globals = new sfParameterHolder();
    sfConfig::set(KK_GLOBALS, $kk_globals);
  }

  if (is_array($key)) {
    foreach ($key as $name => $value) {
      $kk_globals->set($name, $value);
    }
  } else {
    $kk_globals->set($key, $value);
  }
}

/**
 * Call once in the main layout template to output all KK.* globals.
 */
function kk_globals_out()
{
  kk_globals_put('BASE_URL', url_for('@homepage', true));

  if (null !== ($kk_globals = sfConfig::get(KK_GLOBALS))) {
    $values = json_encode($kk_globals->getAll());

    $lines = [];
    foreach ($kk_globals->getAll() as $name => $value) {
      $lines[] = "KK.{$name} = ".json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).';';
    }

    echo "\n<script>\nwindow.KK || (KK = {});\n".implode("\n", $lines)."\n</script>\n";
  }
}

function with_footer() {
  sfContext::getInstance()->getRequest()->setParameter('_homeFooter', '1');
}

class koohiiConfiguration extends sfApplicationConfiguration
{
  private float $profile_time;

  public function configure()
  {
    $this->profileStart();
  }

  public function initialize()
  {
    // because of sf cache:clear repeating this code??
    if (!defined('CORE_ENVIRONMENT')) {
      // FIXME .. refactor to sf
      define('CORE_ENVIRONMENT', $this->getEnvironment());

      define('KK_ENV_DEV', CORE_ENVIRONMENT === 'dev');
      define('KK_ENV_PROD', CORE_ENVIRONMENT === 'prod' || CORE_ENVIRONMENT === 'linode');

      define('KK_ENV_FORK', sfConfig::get('app_fork'));

      // FIXME obsolete, clean up
      define('CJ_MODE', 'rtk');
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

    if (null === $profiler) {
      return $totaltime;
    }

    $phptime = $totaltime - $profiler->getQueryTime();
    $sqltime = $profiler->getQueryTime();

    if ($totaltime > 0) {
      $percentphp = number_format(($phptime / $totaltime) * 100, 2);
      $percentsql = number_format(($sqltime / $totaltime) * 100, 2);
    } else {
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
