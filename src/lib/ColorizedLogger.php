<?php
/*
 * Simple file logger with colorized output.
 *
 *   - colorized output to make it easier to parse
 *   - cleaner output than error_log()
 *   - dump variables into a readable format
 *
 * USE ONLY FOR LOCAL DEBUGGING.
 *
 * Usage:
 *
 *   LOG::out('message');
 *   LOG::out('Request params: ', $_REQUEST);
 *   LOG::out($_COOKIE);
 *
 *
 * To view the log:
 *   $ tail -f ./koohii-log.txt
 */

use Koohii\Batch\ConsoleFormatter;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

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
    $this->formatter = new ConsoleFormatter();

    // directory for log files (use sf1.x's root path).
    $fileName = $this->getSfRootDir().'/'.self::LOG_FILE_NAME;

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

    if (null === $sfRootDir) {
      throw new Exception('ERROR: sf_root_dir unavailable.');
    }

    return $sfRootDir;
  }

  /**
   * @return resource
   */
  private function getFileHandle(string $fileName)
  {
    if (false === ($handle = fopen($fileName, 'a'))) {
      throw new Exception(__CLASS__.":: Can't create/open {$fileName}!");
    }

    return $handle;
  }

  /**
   * @param mixed ...$args Message string, or variable to dump if only 1 argument.
   *                       Optionally pass a 2nd argument to dump alongside the message.
   */
  public static function out(mixed ...$args)
  {
    if (!count($args)) {
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
   *
   * @param array{tag?: string, msg?: string, var?: mixed} $args
   */
  private function output(array $args = [])
  {
    $txt_tag = $args['tag'] ?? ' --- ';
    $txt_msg = $args['msg'] ?? '(no message)';

    $formatter = $this->formatter;
    $handle    = $this->fileHandle;

    $output = [];

    // visual separation to help separate new log lines after reloading php page
    if ($this->lineNr === 0) {
      $output[] = $formatter
        ->setForeground('red')
        ->setOption('bold')
        ->apply("\n".str_repeat('- ', 40)."\n\n")
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
      .' '
    ;

    $output[] = $formatter
      ->setForeground('white')
      ->apply($txt_msg)
    ;

    if ($args['var'] !== self::UNDEFINED) {
      $txt_var = $this->dump_to_string($args['var']);

      $output[] = $formatter
        ->setForeground('yellow')
        ->apply(' '.$txt_var)
      ;
    }

    fwrite($this->fileHandle, implode('', $output)."\n");

    $this->lineNr++;
  }

  private function dump_to_string(mixed $var): string
  {
    $cloner = new VarCloner();
    $dumper = new CliDumper();

    // enable CLI colored output
    $dumper->setColors(true);

    // true to return as string instead of echoing
    return $dumper->dump($cloner->cloneVar($var), true);
  }
}
