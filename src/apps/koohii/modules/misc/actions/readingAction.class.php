<?php

class readingAction extends sfAction
{
  public function execute($request)
  {
    // get custom keywords ONLY for existing flashcards
    $userId = $this->getContext()->getUser()->getUserId();
    $keywords = CustkeywordsPeer::getCustomKeywords($userId, true);

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
