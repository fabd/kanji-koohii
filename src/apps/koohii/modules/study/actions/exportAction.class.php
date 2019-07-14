<?php
class exportAction extends sfAction
{
  /**
   * Export user's stories to CSV.
   * 
   * Note! 'col_escape' option must match the select from StoriesPeer::getSelectForExport()
   *
   */
  public function execute($request)
  {
    $response = $this->getResponse();
    $response->setContentType('text/plain; charset=utf-8');

    $throttler = new RequestThrottler($this->getUser(), 'study.export');
    
    if (!$throttler->isValid()) {
    //  $response->setContentType('text/plain; charset=utf-8');
      $response->setContentType('html');
      return $this->renderPartial('misc/requestThrottleError');
    }

    // get keywords and kanji for story link substitution ({<number>})
    // DISABLED FOR NOW (>_>)
    //$this->exportKeywords = CustkeywordsPeer::getExportKeywords($this->getUser()->getUserId());

    $db      = sfProjectConfiguration::getActive()->getDatabase();
    $csv     = new ExportCSV($db);
    $select  = StoriesPeer::getSelectForExport($this->getUser()->getUserId());

    $fetchMode   = $db->setFetchMode(coreDatabase::FETCH_NUM);
    $tabularData = $db->fetchAll($select);
    $db->setFetchMode($fetchMode);
 // DBG::printr( $tabularData);exit;

    $csvText = $csv->export(
      $tabularData,
      ['framenr', 'kanji', 'keyword', 'public', 'last_edited', 'story'],
      [
        'col_escape' => [0, 0, 1, 0, 0, 1]
        // disable the kanji links notation `{123}` because of headaches with the old/new indexes
        // 'row_callback' => array($this, 'exportStoriesCallback')
      ]
    );
  
    $throttler->setTimeout();
    $this->getResponse()->setFileAttachmentHeaders('my_stories.csv');
    
    $this->setLayout(false);

    return $this->renderText($csvText);
  }

  /**
   * Callback for CSV export of user's Stories.
   *
   * This callback replaces story links with the linked kanji and keyword,
   * so they are formatted similarly to the Study page.
   *
  public function exportStoriesCallback($row)
  {
    $story = preg_replace_callback('/\{(\d+)\}/', array($this, 'exportStoriesReplaceCallback'), $row[5]);
    $row[5] = $story;
    return $row;
  }

  public function exportStoriesReplaceCallback($matches)
  {
    $key = $matches[1];
    return '*'.$this->exportKeywords[$key]['keyword'].'* (FRAME '.$key.')';
  }
   */
}
