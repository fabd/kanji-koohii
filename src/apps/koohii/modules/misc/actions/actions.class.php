<?php

class miscActions extends sfActions
{
  public function executeIndex($request)
  {
  }

  public function executeReading($request)
  {
    // get the Heisig keywords coalesced with user's customized keywords
    $userId = $this->getContext()->getUser()->getUserId();
    $keywords = CustkeywordsPeer::getCoalescedKeywords($userId);
    // DBG::printr($keywords);exit;

    $keywordsMap = [];
    foreach ($keywords as $ucsId => $data)
    {
      $keywordsMap[] = [(int) $ucsId, $data['keyword']];
    }
    // DBG::printr(count($keywordsMap));exit;

    $knownKanji = ReviewsPeer::getKnownKanji($userId);

    $indexMap = rtkIndex::getSequenceMap();

    sfProjectConfiguration::getActive()->loadHelpers(['Bootstrap']);
    kk_globals_put('USER_KEYWORDS_MAP', $keywordsMap);
    kk_globals_put('USER_KNOWN_KANJI', $knownKanji);
    kk_globals_put('RTK_INDEX_MAP', $indexMap);
  }
}
