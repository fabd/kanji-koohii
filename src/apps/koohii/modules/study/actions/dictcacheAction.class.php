<?php
/**
 * Cancelled "Kanji Recognition" feature, code moved to a separate action
 * class so it doesn't need to be loaded with study actions.
 *
 * 2021-10-12
 */
class dictcacheAction extends sfAction
{
  /**
   * API endpoint for dict results cache (retrieve dict results for multiple
   * kanji).
   *
   * @see GetDictCacheFor()
   *
   * Request:
   *   chars           string of kanjis to lookup
   *
   * @param [type] $request
   */
  public function execute($request)
  {
    $params = $request->getParamsAsJson();

    // FIXME : validate
    $chars = $params->chars;
    if (!is_string($chars))
    {
      throw new rtkAjaxException('Bad request.');
    }

    $ucsArr = utf8::toUnicode($chars);

    $results = CacheDictLookupPeer::getDictResultsFor($ucsArr);

    // FIXME - for now it doesn't lookup multiple kanji
    $tron = new JsTron([
      'items' => $results[0] ?? []
    ]);

    return $tron->renderJson($this);
  }
}
