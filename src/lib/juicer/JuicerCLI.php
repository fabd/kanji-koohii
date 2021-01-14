<?php
/**
 * Command line interface to the Juicer tool.
 * 
 * JuicerCLI provides a wrapper for the Juicer tool, that allows to create the output files from
 * the command line. The output files should then be minified with a separate program such as
 * YUI Compressor, or Google Closure compiler. 
 * 
 * Command line options:
 *   See showHelp() method or use --help.
 * 
 * Public methods:
 *   getOption(name, default)    Allows Juicer instance to read command line flags
 * 
 * Config file example (juicer.config.php):
 * 
 *   return array(
 *     // %YUI% will point to the source YUI Library folder
 *     'YUI' => 'C:\Dev\Frameworks\yui\build',
 *     // assets copied from the YUI library will go into a "yui3" folder of the document root 
 *     'MAPPINGS' => array(
 *        'C:\Dev\Frameworks\yui\build' => 'yui3'
 *     )
 *   );
 * 
 * Example (js):
 *  php lib/juicer/JuicerCLI.php -v --webroot web --config config/juicer.config.php --infile web/js/demo.juicy.js
 *
 * Example (css):
 *  php lib/juicer/JuicerCLI.php -v --webroot web --config config/juicer.config.php --infile web/css/demo.juicy.css
 *
 * Vérifier que --strip fonctionne bien:
 *  ack -G 'web/build/.*juiced.js' 'Core\.log'
 *  
 * @author   Fabrice Denis
 * @date     20 Nov 2009
 */

require_once(realpath(dirname(__FILE__).'/..').'/batch/Command_CLI.php');
require_once(SF_ROOT_DIR.'/lib/juicer/Juicer.php');

class Juicer_CLI extends Command_CLI
{
  public function __construct()
  {
    parent::__construct([
      'config|c=s'   => 'Configuration file in php as an array of key => values',
      'webroot|w=s'  => 'Path to the document root of the web server (can be relative)',
      'infile|i=s'   => 'A javascript or stylesheet file to parse',
      'outfile|o=s'  => 'Output file name (defaults to *.juiced.js based on source file)',
      'strip|s-s'    => 'Strip method calls from output (eg. "console.log,console.warn")',
      'list|l'       => 'List all assets used in stylesheets and the remapped web folder'
    ]);

    // check parameters
    $webRoot = $this->getFlag('webroot');
    $infile = $this->getFlag('infile');
    if (null === $webRoot || null === $infile)
    {
      $this->throwError("Missing argument(s)");
    }

    if (!file_exists($infile))
    {
      $this->throwError("File not found: %s", $infile);
    }
    
    // set webroot qualified path
    $webRoot = realpath($webRoot);
    
    // constants file
    $constants = [];
    $configFile = $this->getFlag('config');
    if (!file_exists($configFile))
    {
      $this->throwError("File not found: %s", $configFile);
    }
    else
    {
      $constants = require($configFile);
      $this->verbose('Constants: %s', implode(', ', array_keys($constants)));
    }

    $this->verbose($this->formatter->setForeground('green')->setOption('bold')->apply(
      sprintf('Parsing: "%s" ...', $infile)
    ));

    $options = [
      'VERBOSE'    => $this->getFlag('verbose', false),
      'STRIP'      => $this->getFlag('strip', false),
      'WEB_PATH'   => $webRoot,
      'WEB_EXCL'   => '*.psd,*.txt,*.bak,*.css,*.js',
      'CLI'        => $this   // set if using Juicer from the command line
    ];
    
    // determine output file name
    $outfile = $this->getFlag('outfile');
    if (null === $outfile)
    {
      if (false !== strstr($infile, Juicer::FILE_PATTERN_JUICY))
      {
        $outfile = str_replace(Juicer::FILE_PATTERN_JUICY, Juicer::FILE_PATTERN_JUICED, $infile);
      }
      else
      {
        $this->throwError('--outfile not specifed, the default output file requires "*.juicy.*" pattern.');
      }
    }
  
    $juicer = new Juicer($options, $constants);
    
    // start profiling script speed
    $juicer->profileStart();

    try
    {
      $contents = $juicer->juice($infile);
    }
    catch (Exception $e)
    {
      $this->throwError('(exception): ' . $e->getMessage());
    }

    // end profiling script speed
    $this->verbose('Execution time: %s seconds.', $juicer->profileEnd());

    $this->verbose('Output file: "%s".', $outfile);
    
    if (file_put_contents($outfile, $contents)===false)
    {
      $this->throwError("Error writing to outfile %s", $outfile);
    }
    
    $this->verbose('Success!');
  }
} 

$cmd = new Juicer_CLI();
