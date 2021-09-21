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
    $keywords = CustkeywordsPeer::getCustomKeywords($userId);

    $vueData = [];

    foreach ($keywords as $ucsId => $info)
    {
      //$uri = $this->getContext()->getController()->genUrl('@study_edit?id='.$info['seq_nr']);
      $utfKanji = utf8::fromUnicode($ucsId);

      $vueData[(string) $ucsId] = [
        'kanji' => $utfKanji,
        'keyword' => $info['keyword'],
        'seq_nr' => $info['seq_nr'],
      ];
    }

    sfProjectConfiguration::getActive()->loadHelpers(['Bootstrap']);
    kk_globals_put('READING_KEYWORDS', $vueData);

    // assumes lines end with \r\n
    // $j_text = preg_replace('/[\r\n]+/', '<br/>', $j_text);
  }
}
