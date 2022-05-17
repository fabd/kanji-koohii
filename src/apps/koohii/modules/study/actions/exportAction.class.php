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
      $response->setContentType('html');
      return $this->renderPartial('misc/requestThrottleError');
    }

    // DISABLED FOR NOW (>_>)
    // get keywords and kanji for story link substitution ({<number>})
    //$this->exportKeywords = CustkeywordsPeer::getExportKeywords($this->getUser()->getUserId());

    $userId  = $this->getUser()->getUserId();

    $db      = kk_get_database();
    $csv     = new ExportCSV($db);
    $select  = StoriesPeer::getSelectForExport($userId);

    // [ framenr, kanji, keyword, public, last_edited, text ]
    $fetchMode   = $db->setFetchMode(coreDatabase::FETCH_NUM);
    $tabularData = $db->fetchAll($select);
    $db->setFetchMode($fetchMode);
//DBG::printr( $tabularData);exit;

    // merge the user's starred stories, where there is no personal story
    $this->mergeStarredStories($db, $userId, $tabularData);
//DBG::printr($tabularData);exit;

    // sort by framenr
    usort($tabularData, function ($a, $b) { return $a[1] - $b[1]; });
//DBG::printr($tabularData);exit;

    // use the callback option to remove the UCS col
    $trimUcsCol = function($row) {
      return array_slice($row, 1);
    };

    // NOTE : does not translate kanji links like `{123}` because of headaches with the old/new indexes
    $csvText = $csv->export(
      $tabularData,
      ['framenr', 'kanji', 'keyword', 'public', 'last_edited', 'story'],
      [
        'col_escape'   => [0, 0, 1, 0, 0, 1],
        'row_callback' => $trimUcsCol
      ]
    );

//DBG::printr($csvText);exit;

    $throttler->setTimeout();
    $this->getResponse()->setFileAttachmentHeaders('my_stories.csv');
    
    $this->setLayout(false);

    return $this->renderText($csvText);
  }

  private function mergeStarredStories($db, int $userId, array & $tabularData)
  {
    // 1) get SIDs of starred stories
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    // NOT `join left stories_shared` because if a story is deleted, a vote record can still exist, and sid is NULL
    // GROUP BY  limits starred stories to 1 per ucs_id

    $select = $db->select()
      ->columns(['ss.sid'])
      ->from('storyvotes sv')
      ->join('stories_shared ss', 'ss.ucs_id = sv.ucs_id AND ss.userid = sv.authorid')
      ->where('sv.userid = ? AND sv.vote = 1', $userId)
      ->group('sv.ucs_id');

    $sidArray = $db->fetchCol($select);

    if (count($sidArray) === 0) {
      return;
    }

    // 2) get stories data for that selection of starred stories
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    $select = $db->select()
      ->columns([
        's.ucs_id',
        's.text',
        'u.username',
        'keyword' => CustkeywordsPeer::coalesceExpr()
      ])
      ->from('stories s');
    KanjisPeer::joinLeftUsingUCS($select);
    CustkeywordsPeer::addCustomKeywordJoin($select, $userId);
    $select
      ->joinLeft('users u', 's.userid = u.userid')
      ->where('s.sid IN('.implode(',', $sidArray).')');
    $favStories = $db->fetchAll($select);

    // 3) now merge the starred stories info into the export data,
    //    WHERE the user didn't edit a personal story
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    // get array of kanjis for which the user has a personal story
    $hasOwnStory = array_column($tabularData, 0 /* ucs_id */);

    foreach($favStories as $storyData) {
      $fs_ucs_id  = $storyData['ucs_id'];
      $fs_framenr = rtkIndex::getIndexForUCS($fs_ucs_id);
      $fs_kanji   = rtkIndex::getCharForIndex($fs_framenr);

      if (false === array_search($fs_ucs_id, $hasOwnStory)) {
        // user doesn't have a personal story for this kanji, merge the starred story
        $tabularData[] = [
          $fs_ucs_id,
          $fs_framenr,
          $fs_kanji,
          $storyData['keyword'],
          1,  // public
          '', // last edited
          sprintf('%s (★ story by %s)', $storyData['text'], $storyData['username'])
        ];
      }
    }
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
