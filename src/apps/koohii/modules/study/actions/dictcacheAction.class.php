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

    $fixmeSingleKanjiResults = $results[0];

    $tron = new JsTron([
      'items' => $fixmeSingleKanjiResults,
    ]);

    return $tron->renderJson($this);
  }
}
