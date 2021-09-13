<?php
/**
 * This helper parses Vite's manifest.json into a more optimized format:.
 *
 *   - "flattens" all dependencies for each top-level entry or bundle (`src/*.ts|js`)
 *   - removes any duplicate dependencies
 *   - removes any assets that aren't .css/.js (like images)
 *
 * The helper is used in two places:
 *
 *   - coreWebResponse : when USE_DEV_SERVER is false, each page refresh will pick
 *     up any changes from `vite build --watch` and output css/js tags accordingly
 *   - build_app.php : for production env, the manifest is parsed ahead of time,
 *     and output as a php include file (`config/vite-build.inc.php`) -- this
 *     is probably a minor gain of performance but every little bit adds up.
 *
 * @see lib/core/coreWebResponse.php
 */
class Vite
{
  // MUST match Vite config `build.outDir` (ie. /abs/path inside www folder to Vite build).
  const OUTDIR = '/build/dist';

  public static function getManifestJson()
  {
    $file = sfConfig::get('sf_web_dir').self::OUTDIR.'/manifest.json';

    if (false === ($text = file_get_contents($file)))
    {
      throw new sfException(sprintf('Could not read manifest file "%s".', $file));
    }

    if (null === ($manifest = json_decode($text, true)))
    {
      throw new sfException(sprintf('Error parsing JSON in "%s".', $file));
    }

    $css = [];
    $deps = [];
    $imported = [];

    $parseChunk = function ($chunkInfo) use (&$parseChunk, &$manifest, &$deps, &$css, &$imported)
    {
      // recurse into the dependencies first
      $imports = $chunkInfo['imports'] ?? [];
      foreach ($imports as $importChunk)
      {
        // avoid circular references between imports
        if (!isset($imported[$importChunk]))
        {
          $imported[$importChunk] = true;
          $deps[] = $parseChunk($manifest[$importChunk], $deps, $css);
        }
      }

      // add the chunk's main file (.js)
      $entryFile = $chunkInfo['file'] ?? '-ERROR-';

      // add the chunk's stylesheet(s)
      $styles = $chunkInfo['css'] ?? [];
      foreach ($styles as $cssFile)
      {
        $css[] = $cssFile;
      }

      // DBG::out(' return entryfile '.$entryFile);
      return $entryFile;
    };

    // generate entries with their css/js dependencies "flattened"

    $entries = [];

    foreach ($manifest as $chunkName => $chunkInfo)
    {
      if (0 !== strpos($chunkName, '_'))
      {
        // [
        //   'file' => $manifest[$chunk]['file'],
        //   'deps' => $deps,
        //   'css' => $manifest[$chunk]['css'],
        // ];
        $css = [];
        $deps = [];
        $imported = [];
        $entryFile = $parseChunk($chunkInfo);
        $entries[$chunkName] = [
          'file' => $entryFile,
          'deps' => array_unique($deps),
          'css' => array_unique($css),
        ];
      }
    }

    // echo "<pre>\n".print_r($entries, true)."</pre>"; exit;

    return $entries;
  }
}
