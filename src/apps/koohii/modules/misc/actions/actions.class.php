<?php

class miscActions extends sfActions
{
  public function executeIndex($request)
  {
  }

  public function executeReading($request)
  {
    $userId = $this->getContext()->getUser()->getUserId();
    $keywords = CustkeywordsPeer::getCoalescedKeywords($userId);

    $keywordsMap = array_map(
      fn ($item) => [(int) $item['ucs_id'], $item['keyword']],
      array_values($keywords)
    );

    $knownKanji = ReviewsPeer::getKnownKanji($userId);

    sfProjectConfiguration::getActive()->loadHelpers(['Bootstrap']);
    kk_globals_put('USER_KEYWORDS_MAP', $keywordsMap);
    kk_globals_put('USER_KNOWN_KANJI', $knownKanji);

    // output SEQ_KANJIS for rtk.ts helpers
    rtkIndex::useKeywordsFile();
  }
}
