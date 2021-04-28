<?php
/**
 * Shared Stories List component (paged list).
 * 
 */

class SharedStoriesListComponent extends sfComponent
{
  public function execute($request)
  {
    $db = sfProjectConfiguration::getActive()->getDatabase();

    // the character should be validated earlier in the Study page, but it can also be
    // sent by the paging list POST request
    $ucsId = (int)$request->getParameter('ucsId', 0);

    assert(BaseValidators::validateInteger($ucsId) && $ucsId >= 0x3000);

    $keyword = trim($request->getParameter('keyword', ''));


    $queryParams = $this->getUser()->getLocalPrefs()->syncRequestParams('sharedstorieslist', [uiSelectPager::QUERY_ROWSPERPAGE => 20]);

    // validate against more attempts to pull stories with scripts that abuse the server
    $action = $this->getController()->getActionStack()->getLastEntry()->getActionInstance();
    $action->forward404Unless(preg_match('/^(10|20|50)$/', $queryParams[uiSelectPager::QUERY_ROWSPERPAGE]));

    // optimize the COUNT(*) by avoiding unnecessary JOINs
    $pagerSelect = $db->select('ss.sid')->from('stories_shared ss')->where('ss.ucs_id = ?', $ucsId);

    $this->pager = new uiSelectPager([
      'select'       => $pagerSelect,
      'internal_uri' => 'study/zzzzzz',
      'query_params' => [
        uiSelectPager::QUERY_ROWSPERPAGE => $queryParams[uiSelectPager::QUERY_ROWSPERPAGE]
      ],
      'max_per_page' => $queryParams[uiSelectPager::QUERY_ROWSPERPAGE],
      'page'         => $request->getParameter(uiSelectPager::QUERY_PAGENUM, 1)
    ]);
    $this->pager->init();

    // join decomposition
    /*    
    $select = $db->select('ss.sid')->from('stories_shared ss')->where('ss.ucs_id = ?', $ucsId)
      ->order(array('ss.stars DESC', 'ss.updated_on DESC'))
      ->limitPage($this->pager->getPage() - 1, $this->pager->getMaxPerPage());
    $in_clause = $db->fetchCol($select);

    $select = $db->select(array(
        'ss.userid', 'u.username', 'lastmodified' => 'DATE_FORMAT(ss.updated_on,\'%e-%c-%Y\')',
        's.text', 'ss.stars', 'kicks' => 'ss.reports'
        ))
      ->from('stories_shared ss')
      ->joinLeft('stories s', 'ss.sid = s.sid')
      ->joinLeft('users u', 'u.userid = ss.userid')
      ->where('ss.sid IN ('.implode(',', $in_clause).')')
      ->order(array('ss.stars DESC', 'ss.updated_on DESC'));
      */

    $storiesSelect = $this->pager->applyPaging(
      $this->getSharedStoriesListSelect($ucsId, $keyword, $this->getUser()->getUserId())
    );

    $fetchMode = $db->setFetchMode(coreDatabase::FETCH_OBJ);
    $rows = $db->fetchAll($storiesSelect);
    $db->setFetchMode($fetchMode);
    foreach ($rows as &$row)
    {
      // do not show 0's
      if (!$row->stars) { $row->stars = ''; }
      if (!$row->kicks) { $row->kicks = ''; }

      $row->text   = StoriesPeer::getFormattedStory($row->text, $keyword, true, false);
    }

    $this->rows    = $rows;
    $this->userId  = $this->getUser()->getUserId();
    $this->ucsId   = $ucsId;
    $this->keyword = $keyword;

    return sfView::SUCCESS;
  }

  /**
   * Returns query for the Shared Stories List paging.
   * 
   *   userid
   *   username
   *   lastmodified
   *   stars
   *   kicks
   *   text
   * 
   * @see    study/SharedStoriesComponent
   * 
   * @param int    $ucsId  UCS-2 code value.
   *
   * @return array<array>
   */
  private function getSharedStoriesListSelect($ucsId, $keyword, $userId)
  {
    assert(is_int($ucsId) && $ucsId >= 0x3000);

    $db = sfProjectConfiguration::getActive()->getDatabase();

    // NOTE: must add `public` to select the table partition!

    $select = $db->select([
        'ss.userid', 'u.username', 'lastmodified' => 'DATE_FORMAT(ss.updated_on,\'%e-%c-%Y\')',
        's.text', 'ss.stars', 'kicks' => 'ss.reports'
        ])
      ->from('stories_shared ss')
      ->joinLeft('stories s', 'ss.sid = s.sid')
      ->joinLeft('users u', 'u.userid = ss.userid')
      ->where('ss.ucs_id = ?', $ucsId)
      ->order(['ss.stars DESC', 'ss.updated_on DESC']);

    return $select;
  }
}
