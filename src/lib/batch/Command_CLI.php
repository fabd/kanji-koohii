<?php
/**
 * A base class for creating command line tools for the koohii app.
 *
 * NOTE !!!! This is not the way to do it with symfony, symfony provides a way to make
 * CLI programs, but refactoring my old cli tools was a pointless task.
 *
 *
 * Provides flags to set the application and environment in which the script will run.
 *
 * Features:
 *   - GNU convention: any arguments following -- must be treated as non-option arguments.
 *     For example: "rm -- -filename-with-dash".
 *   - Provides default flags: --app, --env, --verbose, --help
 *   - If no arguments given, shows help text and exits. Otherwise will exit
 *     if any arguments does not match the constructor options
 *     (so no need to test everything).
 *   - Fixes CGI compatibility issues if cgi/fcgi detected.
 *
 * Constructor:
 *   Pass an array of options using Zend_Console_Getopt-compatible format strings:
 *
 *   'flag|f=s'   required string value + short alias
 *   'flag|f-s'   optional string value + short alias
 *   'flag|f=i'   required integer + short alias
 *   'flag|f'     boolean flag + short alias
 *   'flag=s'     required string, no short alias
 *   'flag-s'     optional string, no short alias
 *   'flag'       boolean flag (boolean: 1 when present, null when absent)
 *   'f'          flag, short alias
 *
 * These options are automatically supported:
 *   -v --verbose      Verbose mode
 *   -h --help         Show help
 *
 * These options change the application environment:
 *   --app <app>       Sets the Symfony app (changes the settings file)
 *   --env <env>       Sets the Symfony environment, defaults to "dev"
 *
 * Methods:
 *   __construct(array $options)   Override this and call parent::__construct()
 *
 *   getFlag($name, $default = null)   Returns option value, or default or NULL
 *                                     (specified flag without parameter is 1, unspecified is NULL or default value)
 *
 *   echof($message[, args])           Output sprintf style message
 *   verbose($message[, args])         Output sprintf style message only in verbose mode (-v|--verbose)
 *   throwError($message[, args])      Output sprintf style message to STDERR and exit
 *
 *
 * Utility methods:
 *   getRelativePathFrom($path, $base)
 *
 * @author  Fabrice Denis
 */
define('SF_ROOT_DIR', realpath(dirname(__FILE__).'/../..'));
define('SF_LIB_DIR', realpath(dirname(__FILE__).'/../vendor/symfony/lib'));

// Defaults to use for --app and --env
define('DEFAULT_APP', 'koohii');
define('DEFAULT_ENV', 'dev');

// Composer
require_once SF_ROOT_DIR.'/vendor/autoload.php';

// Console colour output
require_once SF_ROOT_DIR.'/lib/batch/ConsoleFormatter.php';

use GetOpt\ArgumentException;
use GetOpt\GetOpt;
use GetOpt\Option;

class Command_CLI
{
  protected $isVerbose = false;
  protected $formatter;
  private $opts;

  /*
   * Used with rtrim() or ltrim() to clean the ends of path names.
   */
  public const SLASHES_WHITESPACE = " \t\n\r\\/";

  /**
   * Remember to call parent::__construct() first when you override this!
   *
   * Options are specified using Zend_Console_Getopt-compatible format
   * strings as array keys, with descriptions as array values.
   * See class docblock for format details.
   *
   * @param array $options ['flag|f=s' => 'Description', ...]
   */
  public function __construct(array $options)
  {
    $this->formatter = new ConsoleFormatter();

    // add the default options
    $options = array_merge($options, [
      'help|h'    => 'Show help',
      'verbose|v' => 'Verbose mode (show more information)',
      'app-s'     => 'Sets CORE_APP (defaults to "'.DEFAULT_APP.'")',
      'env-s'     => 'Sets CORE_ENVIRONMENT (defaults to "'.DEFAULT_ENV.'")',
    ]);

    $optObjects = [];
    foreach ($options as $key => $desc) {
      $optObjects[] = $this->parseZendOption($key, $desc);
    }

    $this->opts = new GetOpt($optObjects);

    // parse command line
    try {
      $this->opts->process();
    } catch (ArgumentException $e) {
      // eg. "Option 'path' must have a value"
      echo $e->getMessage()."\n\n";
      echo $this->opts->getHelpText();

      exit;
    }

    if ($this->getFlag('help') || !$this->hasArgs()) {
      echo $this->opts->getHelpText();

      exit;
    }

    $opt_app   = $this->getFlag('app', DEFAULT_APP);
    $opt_env   = $this->getFlag('env', DEFAULT_ENV);
    $opt_debug = true;

    // bootstrap symfony app configuration
    try {
      require_once SF_ROOT_DIR.'/config/ProjectConfiguration.class.php';
      $configuration = ProjectConfiguration::getApplicationConfiguration($opt_app, $opt_env, $opt_debug);
      sfContext::createInstance($configuration);
    } catch (Exception $e) {
      echo $e->getMessage()."\n";
      $statusCode = $e->getCode();

      exit(is_numeric($statusCode) && $statusCode ? $statusCode : 1);
    }

    $this->isVerbose = $this->getFlag('v', false);

    $this->verbose("Verbose: ON (APP '%s', ENVIRONMENT '%s')", sfConfig::get('sf_app'), sfConfig::get('sf_environment'));
  }

  /**
   * Translate a Zend_Console_Getopt-style format string into a
   * GetOpt Option object.
   *
   * Format: 'longname|s{suffix}' or 'longname{suffix}' or 's' (single char = short only)
   * Suffix: '=s' required string, '=i' required integer, '-s' optional string, (none) boolean flag
   *
   * @param string $key  Format string (e.g. 'user|u=s', 'verbose|v', 'app-s')
   * @param string $desc Description for help text
   */
  private function parseZendOption(string $key, string $desc): Option
  {
    // Detect and strip suffix: =s, =i (required) or -s (optional)
    $mode = GetOpt::NO_ARGUMENT;
    if (preg_match('/([=\-][si])$/', $key, $m)) {
      $key  = substr($key, 0, -2);
      $mode = ($m[1][0] === '=') ? GetOpt::REQUIRED_ARGUMENT : GetOpt::OPTIONAL_ARGUMENT;
    }

    // Split long|short alias
    $short = null;
    $long  = null;
    if (strpos($key, '|') !== false) {
      [$long, $short] = explode('|', $key);
    } elseif (strlen($key) === 1) {
      $short = $key;
    } else {
      $long = $key;
    }

    return Option::create($short, $long, $mode)->setDescription($desc);
  }

  /**
   * Return command line option, or default value, or null.
   *
   * @param mixed|null $default
   *
   * @return mixed
   */
  public function getFlag(string $name, $default = null)
  {
    $value = $this->opts->getOption($name);

    return $value ?? $default;
  }

  /**
   * Returns true if there are any arguments on the command line.
   *
   * @return bool
   */
  public function hasArgs()
  {
    $argv = $_SERVER['argv'] ?? [];

    return count($argv) > 1;
  }

  /**
   * Prints out sprintf style error message to STDERR and exits.
   */
  public function throwError(string $message, ...$args)
  {
    if (count($args)) {
      $message = call_user_func_array('sprintf', [$message, ...$args]);
    }
    $message .= "\n";

    $this->formatter->setForeground('red');
    $this->formatter->setOption('bold');

    fwrite(STDERR, $this->formatter->apply($message));

    exit(-1);
  }

  /**
   * If verbose flag is set, prints a sprintf style message, otherwise do nothing.
   *
   * @param mixed $message
   */
  public function verbose($message, ...$args)
  {
    if ($this->isVerbose) {
      if (count($args)) {
        $message = call_user_func_array('sprintf', [$message, ...$args]);
      }
      $message .= "\n";

      fwrite(STDERR, $message);
    }
  }

  /**
   * Print a message to the console. "echof" as in "printf style echo".
   */
  public function echof(...$args)
  {
    $message = call_user_func_array('sprintf', $args)."\n";
    fwrite(STDERR, $message);
  }

  /**
   * Return a relative path from an absolute path, given the base path.
   *
   * @param string $path Fully qualified source path, can include filename
   * @param string $base Fully qualified base path (no filename)
   *
   * @return string Relative path, without leading separator
   */
  public function getRelativePathFrom($path, $base)
  {
    $pos = strpos($path, $base);
    if ($pos === false || $pos !== 0) {
      $this->throwError('getRelativePathFrom() path (%s) does not start with base (%s)', $path, $base);
    }

    $relPath = substr($path, strlen($base));

    return ltrim($relPath, self::SLASHES_WHITESPACE);
  }
}
