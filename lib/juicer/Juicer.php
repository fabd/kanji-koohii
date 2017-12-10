<?php
/**
 * Juicer is a php tool that preprocesses, concatenates javascript and stylesheets,
 * and copies images and other assets to the web document root, optionally remapping
 * folders to desired names.
 * 
 * Constructor options:
 *   WEB_PATH   Path to the document root served by the web server. Base folder for copying
 *              assets, base folder for the translated assets urls in stylesheet rules.
 *   WEB_EXCL   File patterns to exclude from asset copying, separated by commas
 *   
 *   VERBOSE    Verbose log of the source file parsing, required files, copied assets
 *   
 *   STRIP      A function call expression without the arguments, to strip from the output.
 *              Separate multiple expressions with comma. Eg "console.log,console.warn"
 *              to remove these Firebug calls.
 * 
 * 
 * How STRIP works:
 *   The strip feature looks for method calls that have only spaces between the start
 *   of line and the method call. The entire line of code is stripped, because it's
 *   difficult to properly match a function call with string arguments.
 *
 *   This is meant to be used with debug logs (eg: console.log()) and assertions that
 *   stand on their own line, and should not be used within other expressions:
 *
 *   GOOD
 *     foo.bar();  // comment
         => The comment is removed, but that's ok.
 *
 *   BAD
 *     if (foo.bar())
 *       => Don't use the method call within other expressions.
 *     foo.bar(); somethingElse();
 *       => Don't place other expressions on the same line.
 *
 * Command line options:
 *   For command line usage, see JuicerCLI.php documentation. 
 *
 *
 * UPDATES
 *
 *   v1.1
 *   - Absolute URLs are ignored, warning issued in CLI (with -v).
 * 
 * 
 * @author   Fabrice Denis
 * @version  1.1
 */

class Juicer
{
  protected
    $options        = null,
    $constants      = array(),
    $mappings       = array(),
    $isVerbose      = false,
    $webPath        = null,
    $curFile        = '',
    $alreadyParsed  = array(),
    $translatedUrls = array(),
    $cli            = null;
    
  const
    /**
     * Used with rtrim() or ltrim() to clean the ends of path names.
     */
    SLASHES_WHITESPACE  = " \t\n\r\\/",
    
    /**
     * Pattern used to match constants in source file.
     * 
     * Do not accept newline characters in the middle, case sensitive
     * 
     * Using the dollar sign, which is a valid in a javascript variable name, because
     * that will not generate validation warnings in a javascript editor.
     */
    PREG_CONSTANT       = '|\%(\w+)\%|',
    
    FILE_PATTERN_JUICY  = '.juicy.',

    FILE_PATTERN_JUICED = '.juiced.';


  /**
   * Constructor.
   * 
   * @param $options
   * @param $constants
   */
  public function __construct($options, $constants = array())
  {
    $this->options        = $options;
    $this->constants      = $constants;
    $this->mappings       = $this->getConstant('MAPPINGS', array());
    $this->isVerbose      = $this->getOption('VERBOSE', false);
    $this->webPath        = realpath($this->removeTrailingSlash($this->getOption('WEB_PATH')));
    $this->alreadyParsed  = array();
    $this->translatedUrls = array();
    $this->cli            = $this->getOption('CLI', false);

    $this->stripLogs      = $this->getOption('STRIP', false);
    if ($this->stripLogs !== false) {
      $this->verbose('Strip ON: remove all %s calls from output file.', $this->stripLogs);
    }

    $this->ignoreAssets  = array();
    $excludePatterns = explode(',', $this->getOption('WEB_EXCL'));
    foreach ($excludePatterns as $pattern)
    {
      $pattern = preg_quote(trim($pattern), '|');
      // note '*' was quoted as '\*'
      $pattern = '|^' . str_replace('\\*', '.+', $pattern) . '$|i';
      $this->ignoreAssets[] = $pattern;
    }
    //die(implode(' - ', $this->ignoreAssets));
  }

  /**
   * Parse the source file and copy required assets to the web path.
   * 
   * @param  string $infile  Source file to parse and "build".
   * @return string  Result of parsed source file (concatenated scripts)
   */
  public function juice($infile)
  {
    // the web folder must exist already
    if (true !== is_dir($this->webPath)) {
      $this->throwException('The web path "%s" does not exist.', $this->webPath);
    }

    $srcFile = realpath($infile);

    $buffer = $this->requireFile($srcFile, $this->webPath);
    
    // strip code from resulting file
    if ($this->stripLogs !== false) {
      $buffer = $this->stripOutput($buffer, $this->stripLogs);
    }
    
    // css urls
    if ($this->getFileExtension($infile) === 'css') {
      $buffer = $this->translateUrlCleanup($buffer);
    }
    
    // verbose logs (command line only)
    if ($this->cli)
    {
      if ($this->cli->getFlag('list'))
      {
        $total = count($this->translatedUrls);
        if ($total) {
          $list = array();
          foreach ($this->translatedUrls as $src => $dst) {
            $list[] = ' <WEB_PATH>/' . $dst;
          }
          
          $this->verbose("\nList of assets used by stylesheets:\n\n" . implode("\n", $list));
        }
        $this->verbose("\n %d assets referenced in stylesheets.\n", $total);
      }
    }
    
    return $buffer; 
  }
  
  /**
   * Include and parse the contents of a "juicy" file (a file that can have Juicer commands :)).
   * 
   * From path defaults to the current file location (absolute)
   * 
   * @param string $srcFile    Current file's realpath
   * @param string $from       Current setting of the require from command (=base path for includes).
   * 
   */
  protected function requireFile($srcFile, $from)
  {
    if (!file_exists($srcFile)) {
      $this->throwException(' File does not exist: "%s"', $srcFile);
    }

    // parse the source file
    $handle = @fopen($srcFile, "r");
    if ($handle)
    {
      // 
      $lineNr = 1;
      $this->curFile = $srcFile;

      // skip the BOM mark, if any
      $this->skipUTF8BomMark($handle);
      
      // start buffering content of this file
      ob_start();

      $requireFrom = $from;

      while (!feof($handle))
      {
        $buffer = fgets($handle, 4096);

        // parse commands
        if (strncmp($buffer, '/* =', 4)===0)
        {
          $buffer = $this->parseCommand(substr($buffer, 4), $requireFrom, $srcFile, $lineNr);
        }

        // echo to the current buffer
        echo $buffer;
        
        $lineNr++;
      }
      fclose($handle);

      // get the parsed content of this file
      $buffer = ob_get_clean();

      // now we can do css assets url remapping
      if ($this->getFileExtension($srcFile) === 'css') {
        // $buffer = $this->cleanComments($buffer);
        $buffer = $this->translateAssetsUrls($buffer, $srcFile, $from);
      }
      
      // replace constants
      $buffer = $this->substituteConstants($buffer);
    }
    else
    {
      $this->throwException('Error trying to read file "%s".', $file);
    }

    return $buffer . "\n";
  }

  /**
   * Move the file pointer past the UTF-8 Byte Order Mark (BOM) if it exists.
   *
   * @see http://en.wikipedia.org/wiki/Byte_order_mark
   * 
   * @param string $handle  File handle (assumed to be at the start of the file)
   */
  protected function skipUTF8BomMark($handle)
  {
    if (!feof($handle))
    {
      $s = fgets($handle, 4);

      //$this->verbose("bom found %s l=%d", $s, strlen($s));

      if ($s === pack("CCC", 0xef, 0xbb, 0xbf))
      {
        $this->verbose("  BOM mark skipped!\n");
        return;
      }
      rewind($handle);
    } 
  }
  
  protected function parseCommand($buffer, &$from, $srcFile, $lineNr)
  {
    if (preg_match('/^require from "([^"]+)"/', $buffer, $matches))
    {
      $constName = '';
      $fromValue = $matches[1];
      if (preg_match(self::PREG_CONSTANT, $fromValue, $matches))
      {
        $constName = $matches[1];
        $fromValue = $this->getConstant($constName);
        if ($fromValue !== NULL) {
          $fromValue = $this->removeTrailingSlash($fromValue);

          // normalize relative paths in Juicer config
          $fromValue = $this->normalizeConfigPath($fromValue);
        }
        else {
          $this->throwException('Woops, constant "%s" is not defined, trying to use at line %d.', $constName, $lineNr);
        }
      }

      
      // set the current path for includes
      $from = $this->normalizeSlashes($fromValue);

      $this->verbose(' =require from %s(%s)"', ($constName!=='' ? '%'.$constName.'% ' : ''), $from);
      
    }
    else if (preg_match('/^require "([^"]+)"/', $buffer, $matches))
    {
      if ($from === null) {
        $this->throwException('Require from path must be set before including a file (=require at line %d).', $lineNr);
      }

      $reqFile = $this->normalizeSlashes($matches[1]);
      $this->verbose(' =require file "%s" (line %d in %s)', $matches[1], $lineNr, $srcFile);
      
      // relative path
      if ($this->isRelativePath($reqFile)) {
        $this->throwException('Relative path in require directive, not implemented yet (use /...)');
      }
      else {
        // $reqFile starts with separator
        $file = $from . $reqFile;        
      }

      //$this->verbose(' =require file "%s" => "%s"', $reqFile, $file);

      // do not parse the same source file twice
      if (!isset($this->alreadyParsed[$file]))
      {
        // set the flag before parsing the file to solve include loop!
        $this->alreadyParsed[$file] = true;
        
        return $this->requireFile($file, $from, $srcFile);
      }
    }
    else if (preg_match('/^provide "([^"]+)"/', $buffer, $matches))
    {
      $path = $this->removeTrailingSlash($matches[1]);
      
      // handle absolute and relative paths
      if (substr($path, 0, 1) === '/') {
        $providePath = $from . $this->normalizeSlashes($path); 
        $this->verbose(' =provide (absolute path): "%s"', $providePath);
      }
      else {
        $providePath = realpath(dirname($srcFile) . DIRECTORY_SEPARATOR . $path);
        $this->verbose(' =provide (relative path): "%s"', $providePath);
      }

      $this->copyAssets($providePath, $from);
    }
    else if (0 !== stripos($buffer, '='))
    {
      // if it's not a /* ====... style header in the comments, could be a Juicer command typo?
      $this->verbose('Improperly formatted Juicer command at line %d ? ==> %s', $lineNr, $buffer);
    }

    return '';
  }
  
  /**
   * 
   *
   */
  private function copyAssets($srcPath, $from)
  {
    // determine the destination folder from the web root
    $destPath = $this->mapAssetsPathToWebPath($srcPath, $from);
    
    //$this->verbose(' copyAssets() from %s to %s', $srcPath, $destPath);
    
    // first create folders from the webpath to the destination assets path,
    // skip those that already exist
    $this->createPath($this->webPath, $destPath);
    
    // copy assets one by one and create sub folders as needed

    $this->copyr($srcPath, $destPath);
  }

  /**
   * Create any number of subfolders, from a start folder.
   * Create the sub folders one by one, if they don't exist already.
   * Throws an exception as soon as it meets an invalid folder.  
   * 
   * @param {string} $startPath  Path to start from, must exist!
   * @param {string} $endPath    The start path with sub folders
   */
  private function createPath($startPath, $endPath)
  {
    $relPath = $this->getRelativePathFrom($endPath, $startPath);
    
    $folders = preg_split('/\//', $relPath);
    $curPath = $startPath;
    foreach ($folders as $folder)
    {
      $curPath = $curPath . DIRECTORY_SEPARATOR . $folder;

      if (false===is_dir($curPath) && false === mkdir($curPath, 0777, true)) {
        $this->throwException('Could not create folder %s', $curPath);
      }
    }
  }

  /**
   * Copy a file, or recursively copy a folder and its contents
   * 
   * Adapted from http://aidanlister.com/repos/v/function.copyr.php by  Aidan Lister
   * 
   * @param       string   $source    Source path
   * @param       string   $dest      Destination path
   * @return      bool     Returns TRUE on success, FALSE on failure
   */
  private function copyr($source, $dest)
  {
    // Check for symlinks
    /* (Fabrice) not sure what this does
    if (is_link($source)) {
      return symlink(readlink($source), $dest);
    }*/

    // Simple copy for a file
    if (is_file($source))
    {
      // skip copy of excluded assets
      if ($this->isExcludedFromCopy($source)) {
        return;
      }

      $this->copyOneAsset($source, $dest);
      return;
    }

    // Make destination directory
    if (!is_dir($dest))
    {
      mkdir($dest);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== ($entry = $dir->read()))
    {
      // skip pointers
      if ($entry == '.' || $entry == '..') {
        continue;
      }

      // deep copy directories
      $this->copyr($source . DIRECTORY_SEPARATOR . $entry, $dest . DIRECTORY_SEPARATOR . $entry);
    }

    // Clean up
    $dir->close();
    return true;
  }
  
  /**
   * Copy one asset file to its destination if it does not exist, or it has been modified.
   * 
   * Uses the modified file time to determine if the destination should be replaced.
   * 
   * @param  string  $source   Fully qualified path of source file
   * @param  string  $dest     Fully qualified path of destination file
   */
  private function copyOneAsset($source, $dest)
  {
    if (is_file($dest))
    {
      // copy if the modified file time is different (not newer)
      $srcTime = filemtime($source);
      $dstTime = filemtime($dest);
      if ($srcTime === $dstTime) {
        return;
      }
    }
    
    if (true === copy($source, $dest))
    {
      // preserve the file modified time (win32)
      if (true === touch($dest, filemtime($source))) {
        return;
      }
    }

    $this->throwException('Error copying asset %s to %s', $source, $dest);
  }

  /**
   * Returns true if the asset with the given filename should be excluded
   * from copying to web folder.  
   * 
   * @param  string $file   Fully qualified file name
   * @return boolean
   */
  protected function isExcludedFromCopy($file)
  {
    foreach ($this->ignoreAssets as $pattern)
    {
      /* paranoia :)
      if (preg_match($pattern, $file) === false) {
        $this->throwException('OOPS  preg_match fails with the pattern %s on file %s', $pattern, $file);
      }*/
      if (preg_match($pattern, $file) === 1) {
        //$this->verbose(' Asset "%s" is ignored (excluded files).', $file);
        return true;
      }
    }
    
    return false;
  }
  
  /**
   * 
   */
  private function substituteConstants($buffer)
  {
    return preg_replace_callback(self::PREG_CONSTANT, array($this, 'substituteConstantCallback'), $buffer);
  }
  
  private function substituteConstantCallback($matches)
  {
    $constName = $matches[1];
    $constValue = $this->getConstant($constName, false);
    
    if (false === $constValue) {
      $this->throwException('The constant "%s" is not defined (used by "%s").', $constName, $this->curFile);
    }
    
    // return the constant as is if not found
    return $constValue;
  }
  
  /**
   * Map all the relative urls in the stylesheet to their new location in the web folder.
   * 
   * @see   translateUrlCallback()
   * 
   * @param string $buffer   String containing stylesheet code
   * @param string $srcFile  Fully qualified source file name
   * @param string $webPath  
   * 
   * @return 
   */
  private function translateAssetsUrls($buffer, $srcFile, $from)
  {
    // trying to avoid globals and curry madness ..
    $this->preg_srcFile = $srcFile;
    $this->preg_from = $from;
   
    // skips urls that have already been translated (those that start with a caret) 
    $buffer = preg_replace_callback('|url\((?=[^#])\\s*["\']?([^)"\']+)["\']?\\s*\)|', array($this, 'translateUrlCallback'), $buffer);
    return $buffer;
  }
  
  /**
   * 
   * @param object $matches
   * @return 
   */
  private function translateUrlCallback(array $matches)
  {
    // get qualified path
    $assetUrl = $this->normalizeSlashes($matches[1]);

    // compute translated url path only once
    if (isset($this->translatedUrls[$assetUrl]))
    {
      $destPath = $this->translatedUrls[$assetUrl];
    }
    else
    {
      $path_parts = pathinfo($assetUrl);
      $assetName = $path_parts['basename'];
  
      // can not work with absolute asset urls
      if (!$this->isRelativePath($assetUrl)) {

        // $this->throwException('Invalid absolute asset url "%s" in source file "%s" (fix: use relative paths).', $matches[1], $this->preg_srcFile);
        
        // TODO : create a warning() function that formats in yellow
        $msg = sprintf('  Warning: absolute url "%s" in file "%s" (use rel. path).', $matches[1], $this->preg_srcFile);
        require_once(SF_ROOT_DIR.'/lib/batch/ConsoleFormatter.php');
        $fmt = new ConsoleFormatter();
        $this->verbose($fmt->setForeground('yellow')->setOption('bold')->apply($msg));

        return 'url(#' . $matches[1] . ')';
      }
  
      // find the qualified asset path in the source location
      $sourcePath = dirname($this->preg_srcFile) . DIRECTORY_SEPARATOR . $assetUrl;
      $assetPath = realpath($sourcePath);

      // check that the asset path is correct, the asset file must exist in the source
      if ($assetPath === false) {
        //$this->throwException('The asset path "%s" is invalid in source file "%s"', $matches[1], $this->preg_srcFile);
        $this->verbose('The asset path "%s" is invalid in source file "%s"', $matches[1], $this->preg_srcFile);
        return 'url(#' . $matches[1] . ')';
      }
 
      // find the location in the web folder it maps to
      $destPath = $this->mapAssetsPathToWebPath(dirname($assetPath), $this->preg_from) . DIRECTORY_SEPARATOR . $assetName;
        
      // now find the relative path from the web path
      $destPath = $this->forwardSlashes($this->getRelativePathFrom($destPath, $this->webPath));
      
      //$this->verbose(' Translated url(%s) to %s', $assetUrl, $destPath);
      
      $this->translatedUrls[$assetUrl] = $destPath;
    }
    
    // prepend url with a special character to avoid translating urls twice, see translateUrlCleanup()
    return 'url(#/' . $destPath . ')';
  } 
  
  /**
   * Resolves references to '/./', '/../' and extra '/' characters in the input path,
   * and returns the canonicalized absolute pathname.
   * 
   * NOTE: UNUSED
   * 
   * @see   http://www.php.net/manual/en/function.realpath.php#84012
   * 
   * @param string $path
   * @return string
   */
  protected function unrealpath($path)
  {
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part)
    {
      if ('.' == $part) {
        continue;
      }
      if ('..' == $part) {
        array_pop($absolutes);
      }
      else {
        $absolutes[] = $part;
      }
    }
    return implode(DIRECTORY_SEPARATOR, $absolutes);
  }
  
  /**
   * Remove the carets we inserted in the stylesheet urls,
   * after all stylesheets have been included and processed.
   * 
   * @param string $buffer
   * @return string 
   */
  private function translateUrlCleanup($buffer)
  {
    return preg_replace('|url\(#|', 'url(', $buffer);
  }
  
  /**
   * Map an asset path to destination path in the web folder.
   * 
   * Eg: D:/Dev/yui2.7.0/build/button/assets  =>   <webPath>/<mapfolder>/<relSrcPath> 
   *  
   * @param string $path  Source path, absolute 
   * @param string $from  
   */
  private function mapAssetsPathToWebPath($srcPath, $from)
  {
    $relPath = $this->getRelativePathFrom($srcPath, $from);
    
    return $this->getWebPath($srcPath) . DIRECTORY_SEPARATOR . $relPath;
  }

  /**
   * Return a relative path from an absolute path, given the base path.
   * 
   * @param  string $path  Fully qualified source path, can include filename
   * @param  string $base  Fully qualified base path (no filename)
   * @return string  Relative path, without leading separator
   */
  private function getRelativePathFrom($path, $base)
  {
    $pos = strpos($path, $base);
    if ($pos === false || $pos !== 0) {

      $this->throwException('getRelativePathFrom() path (%s) does not start with base (%s)', $path, $base);
    }
    
    $relPath = substr($path, strlen($base));
    
    return ltrim($relPath, self::SLASHES_WHITESPACE);
    
  }
  
  /**
   * Returns absolute path to web folder destination, using mapping if defined.
   * 
   * path must be asbolute 
   */
  private function getWebPath($srcPath)
  {
    // todo: mappings: if one of mapped forlders matches beginning of $srcPath, return webPath + mapped path 
    foreach ($this->mappings as $configPath => $relWebPath)
    {
      $absPath = $this->normalizeConfigPath($configPath);

      if (stripos($srcPath, $this->normalizeSlashes($absPath)) === 0)
      {
        // TODO: add diff path (eg. mapping to a sub part of require from path
        // eg:  D:/Dev/build (from), D:/Dev (map to 'foobar')  =>  <webRoot>/foobar/build
        //        extra '/build' path due to difference between mapped folder and require folder
        
        return $this->webPath . DIRECTORY_SEPARATOR . $relWebPath;
      }
    }

    return $this->webPath;
  } 

  /**
   * Normalize paths used in Juicer Config file constants, to absolute paths.
   *
   * Relative paths MUST start with './', are relative to SF_ROOT_DIR (project
   * root).
   *
   */
  private function normalizeConfigPath($path)
  {
    if (false !== stripos($path, './'))
    {
      $absPath = realpath(SF_ROOT_DIR.'/'.$path);
    }
    else if (0 === stripos($path, '/'))
    {
      $absPath = $path;
    }
    else
    {
      $this->throwException(" The path '%s' is invalid (should be ./relative or /absolute", $path);
    }

    return $absPath;
  }

  /**
   * Remove method calls from the output javascript, such as Firebug's console.log().
   * 
   * Note: because it's difficult to match a function expression and all arguments,
   * and string parameters, the ENTIRE LINE is stripped from the output. This is meant
   * to be used with assertions, and debug logs that normally do not span multiple
   * lines.
   *
   * @param string $buffer   Output buffer (javascript)
   * @param string $methods  One or more methods separated by comma (eg "App.log,App.assert").
   * 
   * @return string 
   */
  public function stripOutput($buffer, $methods)
  {
    $m = explode(',', $methods);
    $s = $buffer;

    $orig_len = strlen($s);

    foreach ($m as $expr)
    {
      // this pattern does not properly match ()'s inside the string: Foo.bar('foo.bar()');
      //$pattern = '/' . preg_quote($expr) . '\(\\s*("[^"\\\]*(\\\.[^"\\\]*)*"|[^\)]*)\)[^\\r\\n;]*;' . '/';

      // match the entire line if it contains an uncommented method call
      $pattern = '/^(\s*' . preg_quote($expr) . '\(.*)$/m';

      $count = 0;
      $s = preg_replace($pattern, '', $s, -1, $count);
      if ($count > 0)
      {
        $this->verbose(" Stripped $count method call(s) ($expr)"); 
      }
    }

    $new_len = strlen($s);
    if ($new_len !== $orig_len)
    {
      $this->verbose(" Stripped total: %d bytes.", $orig_len - $new_len);
    }

    return $s;
  }
  
  /**
   * Remove all C style comments from the buffer.
   * 
   * C-style comments starting with /*! are preserved.
   * (same behaviour as YUI Compressor 2.3.3+) 
   * 
   * @param string $s
   * @return string
   */
  public function cleanComments($s)
  {
    //$pattern = '@/\*[^!][\s\S]*?\*/|//.*@';
    $pattern = '@/\*[^!][\s\S]*?\*/|//.*@';


$pattern = '@/\*[^!](?:.|[\r\n])*?\*/@'; 

    return preg_replace($pattern, '', $s);
    //return preg_replace('#/\\*[^!].*?\\*/#', '', $s);
  }

  /**
   * Returns true if path is relative, false if it begins with DIRECTORY_SEPARATOR.
   * 
   * Note! Must use normalizeSlashes() before this function! 
   * 
   * @param string $path  A path with NORMALIZED slashes 
   * @return boolean 
   */  
  protected function isRelativePath($path)
  {
    return (substr($path, 0, 1) !== DIRECTORY_SEPARATOR);
  } 
  
  /**
   * Convert slashes to the system's DIRECTORY_SEPARATOR (backslash on Windows).
   * 
   * @param string $path  A file path
   * @return string
   */
  protected function normalizeSlashes($path)
  {
    return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
  }
  
  /**
   * Makes all slashes forward, as used in web urls.
   * 
   * @return string 
   */
  protected function forwardSlashes($path)
  {
    return str_replace('\\', '/', $path);
  }
  
  /**
   * Trim any unwanted characters from the tail of the pathname,
   * including slash and backslash characters.
   *
   * @return {String}
   */
  private function removeTrailingSlash($path)
  {
    return rtrim($path, self::SLASHES_WHITESPACE);
  }

  /**
   * Returns file extension in lowercase, or an empty string.
   * 
   * @return string   File extension, always lowercase
   */
  private function getFileExtension($path)
  {
    $path_parts = pathinfo($path);
    return isset($path_parts['extension']) ? $path_parts['extension'] : '';
  }

  /**
   * Returns an option value, or the default value if not set.
   * 
   * If both the option value and default value are not set, throws
   * an exception to indicate that the option value is missing.
   * 
   */
  private function getOption($name, $default = null)
  {
    if (($value = $this->getArrayKey($this->options, $name, $default)) === null) {
      $this->throwException(' OPTION "%s" required but not set.', $name);      
    }
    return $value;
  }
  
  /**
   * Similar to getOption(), returns a constant value or default value,
   * throws an exception if using an undefined constant without default value. 
   *
   */
  private function getConstant($name, $default = null)
  {
    if (($value = $this->getArrayKey($this->constants, $name, $default)) === null) {
      $this->throwException(' CONSTANT "%s" used but not set.', $name);      
    }
    return $value;
  }
  
  /**
   * How more generic can you be? :p 
   * 
   * Note: this doesn't support values of the null type.
   */
  private function getArrayKey(array $array, $name, $default = null)
  {
    if (array_key_exists($name, $array)) {
      return $array[$name];
    }
    return $default;
  }
  
  /**
   * Output verbose message to the console.
   *
   */
  private function verbose()
  {
    $args = func_get_args();
    if ($this->isVerbose) {
      $message = call_user_func_array('sprintf', $args) . "\n";
      fwrite(STDERR, $message);
    }
  }

  /**
   * Helper.
   *
   */
  protected function throwException()
  {
    // clean content echoed for the current require() file
    ob_end_clean();
    
    $args = func_get_args();
    $message = call_user_func_array('sprintf', $args);
    throw new Exception($message);    
  }

  /**
   * Begin couting execution time of the script. 
   * 
   */
  public function profileStart()
  {
    $this->time_start = microtime(true);
  }

  /**
   * Return time elapsed by script execution.
   *
   * @return string  
   */
  public function profileEnd()
  {
    $time_diff = microtime(true) - $this->time_start;
    return sprintf('%.3f', $time_diff);
  }
}

/* TESTS (uncomment to test) */
/*
$s = <<< EOD
Core.log(false);
 Core.log();
  Core.log('lolcats');
  Core.log('lolcats')
  //Core.log('commented');
return;
EOD;
echo "\nBUFFER ----------------------------\n".$s;
echo "\n\nRESULT\n".Juicer::stripOutput($s, array('Core.log'));exit;
*/
