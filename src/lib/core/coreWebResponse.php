<?php
/**
 * Extend the response object with versioning of javascript and stylesheet files.
 * 
 * Javascript and stylesheet files use a different naming pattern with a version number,
 * which is redirected by the .htaccess file (mod_rewrite) to a php script.
 * The script returns js & css files compressed with expiry information in the headers.
 *
 * July 2019
 * - for the scss build, grab the scss compiled files in /build/ folder
 * - for *.juicy.js files, maintain Juicer's "hot reload" via mod rewrite
 * 
 * @author  Fabrice Denis
 */

class coreWebResponse extends sfWebResponse
{
  protected
    $resourceVersion = null;
  
  public function clearStuffsRefactorMe()
  {
    $this->javascripts = array_combine($this->positions, array_fill(0, count($this->positions), array()));
    $this->stylesheets = array_combine($this->positions, array_fill(0, count($this->positions), array()));
  }

  /**
   * Adds a stylesheet to the current web response.
   * 
   * @see  sfWebResponse::addStylesheet()
   */
  public function addStylesheet($file, $position = '', $options = array())
  {
    $file = $this->getVersionUrl($file);
    parent::addStylesheet($file, $position, $options);
  }

  /**
   * Adds javascript code to the current web response.
   *
   * @see  sfWebResponse::addJavascript()
   */
  public function addJavascript($file, $position = '', $options = array())
  {
    $file = $this->getVersionUrl($file);
    parent::addJavascript($file, $position, $options);
  }

  /**
   * Return the resource version data, which contains the latest
   * version number (file modified time) for the css and js files in the project.
   * 
   * @return array
   */
  protected function getResourceVersion()
  {
    if ($this->resourceVersion===null)
    {
      $this->resourceVersion = require_once(sfConfig::get('sf_config_dir').'/versioning.inc.php');
    }
    return $this->resourceVersion;
  }

  /**
   * Adds a unique version identifier to the css and javascript file names,
   * (using the local file modified times from build script), to prevent client
   * browsers from using the cache when a css/js file is updated.
   * 
   * The .htaccess files redirects those "versioned" files to a php script that
   * will strip the version number to get the actual file, and return the file
   * gzipped if possible to minimized download size.
   * 
   * @param  string  $url   Css or Javascript url
   * @return string  Resource url with version number in it
   */
  protected function getVersionUrl($url)
  {
    // leave absolute URLs (usually from CDNs like Google and Yahoo) unchanged
    if (stripos($url, 'http:')===0)
    {
      return $url;
    }

    if (sfConfig::get('sf_environment') === 'dev')
    {
      // do not use minified javascript/css in development environment
      $url = str_replace('.min.', '.', $url);

      if (defined('CORE_NOCACHE'))
      {
        // create versioned url like in production but use current timestamp
        // to force the browser to clear its cache on EVERY PAGE LOAD.
        $path = pathinfo($url);
        $ver  = '_v' . time();
        preg_match('/(.+)(\\.[a-z]+)/', $path['basename'], $matches);
        $url  = $path['dirname'] . '/' . $matches[1] . $ver . $matches[2];
        return $url;
      }

      // legacy Juicer JS build, "hot reload" via mod_rewrite
      if (($pos = strpos($url, '.juicy.js')) !== false)
      {
        $url = '/version/cache.php?env=dev&app='.sfConfig::get('sf_app').'&path='.urlencode($url);
      }
      // newer sass build, grab the sass output from the web/build folder
      elseif (($pos = strpos($url, '.build.css')) !== false)
      {
        $url = '/build' . $url;
      }
    }
    else
    {
      // in production, "build" files should be precompiled and minified with a script
      if (($pos = strpos($url, '.juicy.js')) !== false) {
        $url = '/build' . str_replace('.juicy.', '.min.', $url);
      }
      elseif (($pos = strpos($url, '.build.css')) !== false) {
        $url = '/build' . str_replace('.build.', '.min.', $url);
      }

      // add version string
      $versions = $this->getResourceVersion();    
      $path = pathinfo($url);
      $ver = isset($versions[$url]) ? '_v'.$versions[$url] : '';
      preg_match('/(.+)(\\.[a-z]+)/', $path['basename'], $matches);
      $url = $path['dirname'] . '/' . $matches[1] . $ver . $matches[2];
    }
    return $url;
  }
  
  /**
   * Sets response headers for a text response that can be saved
   * as a file by the user. This is useful for exporting data. 
   * 
   * @param string  $fileName  Filename proposed by the browser when the text response is returned
   */
  public function setFileAttachmentHeaders($fileName)
  {
    // set text mode for the data export
    $this->setContentType('text/plain; charset=utf-8');
    
    // disable cache and set file attachment name
    $this->setHttpHeader('Cache-Control', 'no-cache, must-revalidate');
    $this->setHttpHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
    $this->setHttpHeader('Content-Disposition', 'attachment; filename="'.$fileName.'"');
  }
}
