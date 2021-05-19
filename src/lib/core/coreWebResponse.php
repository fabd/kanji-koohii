<?php
/**
 * Extend the response object with versioning of javascript and stylesheet files.
 */
class coreWebResponse extends sfWebResponse
{
  /**
   * Vite javascript assets require this.
   *
   * @see https://vitejs.dev/guide/#browser-support
   */
  const SCRIPT_TYPE_MODULE = ['type' => 'module'];

  /**
   * Vite dev server.
   */
  const VITE_SERVER = 'http://localhost:3000/';
  const VITE_CLIENT = '@vite/client';

  const USE_DEV_SERVER = false;

  /**
   * Adds a stylesheet to the current web response.
   *
   * @see  sfWebResponse::addStylesheet()
   *
   * @param mixed $entryFile
   */
  // public function addStylesheet($file, $position = '', $options = [])
  // {
  //   $file = $this->getVersionUrl($file);
  //   parent::addStylesheet($file, $position, $options);
  // }

  /**
   * Adds javascript code to the current web response.
   *
   * @see  sfWebResponse::addJavascript()
   */
  // public function addJavascript($file, $position = '', $options = [])
  // {
  //   $file = $this->getVersionUrl($file);
  //   parent::addJavascript($file, $position, $options);
  // }

  public function addViteEntry($entryFile)
  {
    static $viteClientLoaded = false;

    if (self::USE_DEV_SERVER && false === $viteClientLoaded)
    {
      $this->addViteClient();
    }

    // with Vite's dev server, just import the entry file(s) which also include the CSS
    if (KK_ENV_DEV && self::USE_DEV_SERVER)
    {
      $this->addJavascript(self::VITE_SERVER.$entryFile, '', self::SCRIPT_TYPE_MODULE);

      return;
    }

    // from this point, add javascripts & stylesheets based on deps in manifest file
    //

    $manifest = $this->getViteManifest();

    $entryInfo = $manifest[$entryFile] ?? [];
    assert(!empty($entryInfo), sprintf("addViteEntry() : entry file not in manifest? '%s'", $entryFile));

    $position = '';

    function addViteDist($assetFile)
    {
      return Vite::OUTDIR.'/'.$assetFile;
    }

    // add imports
    if (null !== ($deps = $manifest[$entryFile]['deps'] ?? null))
    {
      foreach ($deps as $importFile)
      {
        $this->addJavascript(addViteDist($importFile), $position, self::SCRIPT_TYPE_MODULE);
      }
    }

    // add entry file itself
    $importFile = $entryInfo['file'];
    $this->addJavascript(addViteDist($importFile), $position, self::SCRIPT_TYPE_MODULE);

    // add stylesheet(s) if any
    if ($cssDeps = $entryInfo['css'])
    {
      foreach ($cssDeps as $importFile)
      {
        $this->addStylesheet(addViteDist($importFile), $position);
      }
    }
  }

  public function addViteClient()
  {
    $this->addJavascript(self::VITE_SERVER.self::VITE_CLIENT, 'first', self::SCRIPT_TYPE_MODULE);
  }

  protected function getViteManifest()
  {
    static $viteManifest = null;

    if (null === $viteManifest)
    {
      // in dev, grab the manifest.json which is updated in real time by `vite build --watch`
      if (KK_ENV_DEV)
      {
        $viteManifest = Vite::getManifestJson();
      }
      // in test/prod, use the one we pre-parsed to php array
      else
      {
        $viteManifest = require_once sfConfig::get('sf_config_dir').'/vite-build.inc.php';
      }
    }

    return $viteManifest;
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
   * @param string $url Css or Javascript url
   *
   * @return string Resource url with version number in it
   */
  protected function getVersionUrl($url)
  {
    // leave absolute URLs (usually from CDNs like Google and Yahoo) unchanged
    if (stripos($url, 'http:') === 0)
    {
      return $url;
    }

    if (sfConfig::get('sf_environment') === 'dev')
    {
    }
    else
    {
      // legacy bundles compiled with batch/build script
      if (($pos = strpos($url, '.juicy.js')) !== false)
      {
        $url = '/build'.str_replace('.juicy.', '.min.', $url);
      }

      // add version string
      $versions = $this->getResourceVersion();
      $path = pathinfo($url);
      $ver = isset($versions[$url]) ? '_v'.$versions[$url] : '';
      preg_match('/(.+)(\\.[a-z]+)/', $path['basename'], $matches);
      $url = $path['dirname'].'/'.$matches[1].$ver.$matches[2];
    }

    return $url;
  }

  /**
   * Sets response headers for a text response that can be saved
   * as a file by the user. This is useful for exporting data.
   *
   * @param string $fileName Filename proposed by the browser when the text response is returned
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
