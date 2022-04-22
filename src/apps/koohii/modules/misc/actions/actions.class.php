<?php

class miscActions extends sfActions
{
  public function executeIndex($request)
  {
  }

  /**
   * Note: similar to the View All Lessons page, we include the original
   * RTK keywords and kanjis as a javascript file, which is (in theory) more
   * efficient than pulling them from the db (otherwise the full coalesced
   * custom > orig keywords map is ~60 KB in the output html - before gzip).
   *
   * It is the same file that is already included on the Study pages anyway
   * and therefore likely in the browser's cache.
   *
   * We pull the user's customized keywords into a separate map, which is
   * a simpler query - and assuming most users don't customize many keywords.
   *
   * The helpers on the frontend side "coalesce" the custom keywords with
   * the original keywords, for any kanji in the sequence.
   *
   * @param coreRequest $request
   */
  public function executeReading($request)
  {
    $userId = $this->getContext()->getUser()->getUserId();

    $keywordsMap = CustkeywordsPeer::getUserKeywordsMap($userId);

    $knownKanji = ReviewsPeer::getKnownKanji($userId);

    sfProjectConfiguration::getActive()->loadHelpers(['Bootstrap']);
    kk_globals_put('USER_KEYWORDS_MAP', $keywordsMap);
    kk_globals_put('USER_KNOWN_KANJI', $knownKanji);

    // include RTK keywords and kanjis (cf. rtk.ts helpers)
    rtkIndex::useKeywordsFile();
  }
}
