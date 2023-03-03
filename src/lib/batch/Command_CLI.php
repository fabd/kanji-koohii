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
 *   Pass an array of options as for Zend_Console_Getopt constructor.
 *
 * These options are automatically supported:
 *   -v --v            Verbose mode
 *   -h --help         Show help
 *
 * These options change the application environment:
 *   --app <app>       Sets application (changes the settings file, defaults to "revtk")
 *   --env <env>       Sets environment (changes the environment, defaults to "dev")
 *
 * Methods:
 *   __construct(array $zend_getopt)   Override this and call parent::__construct()
 *
 *   getFlag($name, $default = null)   Returns option value, or default or NULL
 *                                     (specified flag without parameter is TRUE, unspecified is NULL or default value)
 *
 *   echof($message[, args])           Output sprintf style message
 *   verbose($message[, args])         Output sprintf style message only in verbose mode (-v|--verbose)
 *   throwError($message[, args])      Output sprintf style message to STDERR and exit
 *
 * Zend_Console_GetOpt ($this->opts):
 *
 *   ->getOption()    Returns value, true if specified without parameter, null if not provided.
 *
 *
 *
 * Utility methods:
 *   getRelativePathFrom($path, $base)
 * 
 * @see     http://framework.zend.com/manual/1.12/en/zend.console.getopt.rules.html
 * 
 * @author  Fabrice Denis
 */

define('SF_ROOT_DIR', realpath(dirname(__FILE__).'/../..'));
define('SF_LIB_DIR', realpath(dirname(__FILE__).'/../vendor/symfony/lib'));

// Defaults to use for --app and --env
define('DEFAULT_APP', 'koohii');
define('DEFAULT_ENV', 'dev');

// Zend/Console/GetOpt
define('ZEND_LIB_DIR', SF_ROOT_DIR.'/lib/vendor');
set_include_path(ZEND_LIB_DIR.PATH_SEPARATOR.get_include_path());
require_once(ZEND_LIB_DIR.'/Zend/Loader.php');
spl_autoload_register(['Zend_Loader', 'autoload']);

// Console colour output
require_once(SF_ROOT_DIR.'/lib/batch/ConsoleFormatter.php');

// Composer
require_once(SF_ROOT_DIR.'/vendor/autoload.php'); 


class Command_CLI
{
  protected
    $isVerbose = false,
    $opts      = null,
    $formatter = null;

  const
    /**
     * Used with rtrim() or ltrim() to clean the ends of path names.
     */
    SLASHES_WHITESPACE  = " \t\n\r\\/";

  /**
   * Remember to call parent::__construct() first when you override this! 
   * 
   * @param  array $zend_getopt   Options for Zend_Console_Getopt
   *
   * @return void
   */
  public function __construct(array $zend_getopt)
  {
    $this->fixCGICompatiblity();

    $this->formatter = new ConsoleFormatter();

    // add the help option
    $zend_getopt = array_merge($zend_getopt, [
      'help|h'      => 'Show help',
      'verbose|v'   => 'Verbose mode (show more information)',
      'app-s'       => 'Sets CORE_APP (defaults to "'.DEFAULT_APP.'")',
      'env-s'       => 'Sets CORE_ENVIRONMENT (defaults to "'.DEFAULT_ENV.'")'
    ]);

    $this->opts = new Zend_Console_Getopt($zend_getopt);

#$db = kk_get_database();
#echo get_class($db);exit;


    // parse command line
    try
    {
      $this->opts->parse();
    }
    catch (Zend_Console_Getopt_Exception $e)
    {
      echo $e->getUsageMessage();
      exit;
    }

    if ($this->getFlag('help') || !$this->hasArgs())
    {
      echo $this->opts->getUsageMessage();
      exit();
    }

    $opt_app = $this->getFlag('app', DEFAULT_APP);
    $opt_env = $this->getFlag('env', DEFAULT_ENV);
    $opt_debug = true;

    // bootstrap symfony app configuration
    try
    {
      require_once(SF_ROOT_DIR.'/config/ProjectConfiguration.class.php');
      $configuration = ProjectConfiguration::getApplicationConfiguration($opt_app, $opt_env, $opt_debug);
      sfContext::createInstance($configuration);

      // $statusCode = 1; //$application->run();
    }
    catch (Exception $e)
    {
      //$application->renderException($e);
      echo $e->getMessage()."\n";
      $statusCode = $e->getCode();

      exit(is_numeric($statusCode) && $statusCode ? $statusCode : 1);
    }

    $this->isVerbose = $this->getFlag('v', false);

    $this->verbose("Verbose: ON (APP '%s', ENVIRONMENT '%s')", sfConfig::get('sf_app'), sfConfig::get('sf_environment'));
  }

  /**
   * This should be overridden, included as a template.
   * 
   * @return string
   */
  /*
  protected function showHelp()
  {
    global $argv;

    define('COL_ALIGN', 24);
    define('OPT_PREFIX', '  --');

    if (!isset($this->cmdHelp)) {
      $this->throwError('Help text must be declared in extending class, see '.__CLASS__.' documentation.');
    }

    $help = $this->cmdHelp;

    // short description
    echo $help['short_desc']."\n\n";

    // sample command line
    echo 'php ' . $argv[0] . ' ' . $help['usage_fmt']."\n\n";

    $options = array_merge($help['options'], array(
      'v'     => 'Verbose mode (off by default)',
      'app'   => 
      'env'   => 
    ));

    // print out list of flags
    foreach ($options as $optFlag => $optDesc)
    {
      $align  = max(COL_ALIGN - strlen(OPT_PREFIX) - strlen($optFlag), 1);

      // align newlines to the description column
      $optDesc = preg_replace('/\n/', "\n".str_repeat(' ', COL_ALIGN), $optDesc);

      echo OPT_PREFIX.$optFlag.str_repeat(' ', $align).$optDesc."\n";
    }
  }*/

  /**
   * Return command line option, or default value.
   * 
   * Throws error if option is undefined and no default value is provided. 
   * 
   * @param string $name
   * @param mixed $default
   * @return mixed
   */
  public function getFlag($name, $default = null)
  {
    $value = $this->opts->getOption($name);
    return null !== $value ? $value : $default;
  }


  /**
   * Returns true if there are any arguments on the command line.
   * 
   * @return boolean
   */
  public function hasArgs()
  {
    $args = $this->opts->toArray();
    return count($args) > 0;
  }

  /**
   * Prints out sprintf style error message to STDERR and exits.
   * 
   * @param string $message
   * @param mixed  $arguments   Variable number of sprintf style arguments
   *
   * @return void
   */
  public function throwError()
  {
    $args = func_get_args();

    // skip sprintf can avoid some formatting issues with sprintf characters
    $message = (count($args) > 1 ? call_user_func_array('sprintf', $args) : $args[0]) . "\n";

    $this->formatter->setForeground('red');
    $this->formatter->setOption('bold');

    fwrite(STDERR, $this->formatter->apply($message));
    exit(-1);
  }
  
  /**
   * If verbose flag is set, prints a sprintf style message, otherwise do nothing.
   *
   * @param string $message
   * @param mixed  $arguments   Variable number of sprintf style arguments
   * 
   * @return void
   */
  public function verbose()
  {
    $args = func_get_args();
    if ($this->isVerbose)
    {
      $message = call_user_func_array('sprintf', $args) . "\n";
      fwrite(STDERR, $message);
    }
  }

  
  /**
   * Print a message to the console. "echof" as in "printf style echo"
   * 
   * @param string $message
   * @param mixed  $arguments   Variable number of sprintf style arguments
   * 
   * @return void

   */
  public function echof()
  {
    $args = func_get_args();
    $message = call_user_func_array('sprintf', $args) . "\n";
    fwrite(STDERR, $message);
  }

  /**
   * Return a relative path from an absolute path, given the base path.
   * 
   * @param  string $path  Fully qualified source path, can include filename
   * @param  string $base  Fully qualified base path (no filename)
   *
   * @return string        Relative path, without leading separator
   */
  public function getRelativePathFrom($path, $base)
  {
    $pos = strpos($path, $base);
    if ($pos === false || $pos !== 0)
    {
      $this->throwError('getRelativePathFrom() path (%s) does not start with base (%s)', $path, $base);
    }

    $relPath = substr($path, strlen($base));

    return ltrim($relPath, self::SLASHES_WHITESPACE);
  }

  /**
   * Tweak the environment to make the PHP CGI binary behave more or less
   * the same way as the CLI binary.
   * 
   * Things to whatch for:
   * - It changes automatically the current working directory to the running script (php -C)
   * - It outputs headers (php -q)
   *
   * @see  http://articles.sitepoint.com/article/php-command-line-1/3
   */
  private function fixCGICompatiblity()
  {
    //echo 'fixCGICompatiblity()'.php_sapi_name()."\n";
    if (version_compare(PHP_VERSION, '4.3.0', '<') || substr(php_sapi_name(), 0, 3) === 'cgi')
    {
      // Handle output buffering
      @ob_end_flush();
      ob_implicit_flush(true);

      // PHP ini settings
      set_time_limit(0);
      ini_set('track_errors', true);
      ini_set('html_errors', false);
      ini_set('magic_quotes_runtime', false);

      // Define stream constants
      define('STDIN', fopen('php://stdin', 'r'));
      define('STDOUT', fopen('php://stdout', 'w'));
      define('STDERR', fopen('php://stderr', 'w'));

      // Close the streams on script termination
      register_shutdown_function(
        function () { fclose(STDIN); fclose(STDOUT); fclose(STDERR); return true; }
      );
    }
  }
}

