<?php
/**
 * Simple file logger with colorized output.
 *
 *   - colorized output to make it easier to parse
 *   - no excess information from error_log(), cleans up the output
 *   - dump variables into a readable format
 *
 * USE ONLY FOR LOCAL DEBUGGING.
 *
 * Usage:
 *
 *   Message (string):
 *     LOG::info('message');
 *
 *   Message with 2nd argument to dump in a readable way:
 *     LOG::info('Request params: ', $request->getParameterHolder()->getAll());
 *
 *   No message, just dump 1st argument:
 *     LOG::info($_COOKIE);
 *
 *
 * To view the log:
 *
 *   $ tail -f ./koohii-log.txt
 */
class LOG
{
  // name of the log file to be created/updated, relative to sf root
  private const LOG_FILE_NAME = 'koohii-log.txt';

  private $fileHandle;

  // use the (modifed) ConsoleFormatter from Symfony1.x
  private ?ConsoleFormatter $formatter = null;

  private int $lineNr = 0;

  private static self $instance;

  private const UNDEFINED = PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;

  public static function inst(): self
  {
    self::$instance ??= new self();

    return self::$instance;
  }

  public function __construct()
  {
    $this->formatter = $this->getFormatter();

    // directory for log files (use sf1.x's root path).
    $fileName = $this->getSfRootDir().'/'.self::LOG_FILE_NAME;

    $this->checkLogFile($fileName);

    $this->fileHandle = $this->getFileHandle($fileName);
  }

  public function __destruct()
  {
    fclose($this->fileHandle);
    $this->fileHandle = null;
  }

  private function getSfRootDir(): string
  {
    $sfRootDir = sfConfig::get('sf_root_dir');

    if (null === $sfRootDir)
    {
      throw new Exception('ERROR: sf_root_dir unavailable.');
    }

    return $sfRootDir;
  }

  private function getFormatter()
  {
    require_once realpath($this->getSfRootDir().'/lib/batch/ConsoleFormatter.php');

    return new ConsoleFormatter();
  }

  // create a writable log file if it doesn't exist
  private function checkLogFile(string $fileName)
  {
    if (!file_exists($fileName))
    {
      if (false === ($handle = fopen($fileName, 'w')))
      {
        exit(__CLASS__.":: Can't create log file {$fileName}.");
      }

      fclose($handle);
    }
  }

  /**
   * @return resource
   */
  private function getFileHandle(string $fileName)
  {
    if (false === ($handle = fopen($fileName, 'a')))
    {
      throw new Exception(__CLASS__.":: Can't open {$fileName}!");
    }

    return $handle;
  }

  /**
   * @param mixed $message Message -- or variable to dump if only 1 argument
   * @param mixed $vardump Optional variable to dump (if 1st arg is the message)
   */
  public static function info()
  {
    $args = func_get_args();

    // in case we mistakenly committed a LOG::info() somewhere
    //  (cf. koohiiConfiguration.class.php)
    if (defined('KK_ENV_PROD') && KK_ENV_PROD === true)
    {
      return;
    }

    if (!count($args))
    {
      throw new Exception(__CLASS__.': no arguments');
    }

    $message = count($args) > 1 ? array_shift($args) : '...';
    $vardump = $args[0];

    self::inst()->output([
      'msg' => $message,
      'tag' => ' INFO ',
      'var' => $vardump,
    ]);
  }

  /**
   * Write to log file with colorized output.
   */
  private function output(array $args = [])
  {
    $txt_tag = $args['tag'] ?? ' --- ';
    $txt_msg = $args['msg'] ?? '(no message)';

    $formatter = $this->formatter;
    $handle = $this->fileHandle;

    $output = [];

    // visual separation to help separate new log lines after reloading php page
    if ($this->lineNr === 0)
    {
      $output[] = $formatter
        ->setForeground('red')
        ->setOption('bold')
        ->apply("\n- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -\n\n")
      ;
    }

    // $output = [
    //   $formatter
    //     ->setForeground('yellow')
    //     ->apply(sprintf('%03d', self::$lineNr)),
    // ];

    $output[] = $formatter
      ->setForeground('black')
      ->setBackground('green')
      ->apply($txt_tag)
      .' ';

    $output[] = $formatter
      ->setForeground('white')
      ->apply($txt_msg)
    ;

    if ($args['var'] !== self::UNDEFINED)
    {
      $txt_var = $this->dump($args['var']);
      $output[] = $formatter
        ->setForeground('yellow')
        ->apply(' '.$txt_var)
      ;
    }

    fwrite($this->fileHandle, implode('', $output)."\n");

    ++$this->lineNr;
  }

  private function dump($expr)
  {
    $s = '';

    if (is_bool($expr))
    {
      $s = $expr === true ? 'true' : 'false';
    }
    elseif (is_null($expr))
    {
      $s = 'null';
    }
    elseif (is_string($expr))
    {
      $s = '"'.addcslashes($expr, "\0..\37\n\r\t\v").'"';
    }
    elseif (is_object($expr) || is_array($expr))
    {
      // ob_start();
      // var_dump($expr);
      // $s = ob_get_contents();
      $s = var_export($expr, true);
    }
    else
    {
      $s = $expr;
    }

    return $s;
  }
}
