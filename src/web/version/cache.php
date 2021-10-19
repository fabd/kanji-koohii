<?php
/**
 * Outputs resource with gzip compression and far future expire headers.
 * 
 * Query parameters (set by htaccess redirection):
 * 
 *   file   Absolute path from the web root to resource file
 * 
 * CHANGELOG
 * 
 *   Oct 2021 ... updated to match Vite build hashes
 *   
 * @author  Fabrice Denis
 */

class CacheResource
{
  const
    RELATIVE_PATH_TO_WEB  = '../.',
    RELATIVE_PATH_TO_ROOT = '../..',
    USE_GZIP_ENCODING     = true;
  
  function __construct()
  {
  }
  
  function execute()
  {
    $filepath = $this->getParameter('file');

    // on web server the path doesn't come with a leading slash, go figure
    if (strpos($filepath, DIRECTORY_SEPARATOR) !== 0) {
      $filepath = DIRECTORY_SEPARATOR . $filepath;
    }
    
    // ignore paths with a '..'
    if (preg_match('!\.\.!', $filepath)) {
      $this->throw404('error1');
    }

    // does the file exist?
    if (!file_exists(self::RELATIVE_PATH_TO_WEB . $filepath)) {
      $this->throw404('error3');
    }

    header("Expires: ".gmdate("D, d M Y H:i:s", time()+315360000)." GMT");
    header("Cache-Control: max-age=315360000");
    
    // output a mediatype header
    $extension = substr(strrchr($filepath, '.'), 1);
    switch ($extension)
    {
      case 'css':
        header("Content-type: text/css");
        break;
      case 'js':
        header("Content-type: text/javascript");
        break;
      // script should be called only for js and css files!  
      default:
        $this->throw404('error4');
        break;
    }

    // don't use gzip compression on IE6 SP1 (hotfix  http://support.microsoft.com/default.aspx?scid=kb;en-us;823386&Product=ie600)
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $IE6bug = (strpos($ua, 'MSIE 6') && strpos($ua, 'Opera') == -1);
    
    // For some very odd reason, "Norton Internet Security" unsets this
    $_SERVER['HTTP_ACCEPT_ENCODING'] = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';

    if (self::USE_GZIP_ENCODING && !$IE6bug && extension_loaded('zlib') && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false))
      ob_start('ob_gzhandler');
    else
      ob_start();

    echo file_get_contents(self::RELATIVE_PATH_TO_WEB . $filepath);
    
    ob_end_flush();
  }
  
  private function getParameter($name, $default = null)
  {
    $value = isset($_GET[$name]) ? $_GET[$name] : $default;
    if ($value === null) {
      $this->throw404('Missing required parameter "%s"', $name);      
    }
    return $value;
  }
  
  private function throw404()
  {
    header("HTTP/1.0 500 Error");
    $args = func_get_args(); 
    $message = call_user_func_array('sprintf', $args);
    echo('Error HTTP 500: ' . $message);
    exit;
  }
}

$o = new CacheResource();
$o->execute();
