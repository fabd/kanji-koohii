<?php

/**
 * Dictionary Lookup for the Study and Flashcard Review pages.
 * 
 * 
 * @author     Fabrice Denis
 */

class DictStudyComponent extends sfComponent
{
  /**
   * Component vars:
   *
   *   ucs_id      UCS2 code of the character to lookup.
   * 
   */
  public function execute($request)
  {
    $select = rtkLabs::getSelectForDictStudy($this->ucs_id);
    $result = sfProjectConfiguration::getActive()->getDatabase()->fetchAll($select);

    $kanji = utf8::fromUnicode(array($this->ucs_id));

    mb_regex_encoding('UTF-8');

    foreach ($result as &$r)
    {
      $r['compound'] = mb_ereg_replace($kanji, '<u>'.$kanji.'</u>', $r['compound']);
    }

    $this->rows = $result;
  }
}

