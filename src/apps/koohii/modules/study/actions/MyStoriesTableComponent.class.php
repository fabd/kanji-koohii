<?php
/**
 * MyStoriesTable Component.
 *
 * Component parameters:
 *
 *   stories_uid
 *   profile_page
 * 
 */

class MyStoriesTableComponent extends sfComponent
{
  public function execute($request)
  {
    // component parameters
    $userId    = $this->stories_uid;
    $isProfile = $this->profile_page;

    // ensure public=1 if tampering with form on profile page
    $isSelf    = $this->getUser()->getUserId() === $userId;

    $action = $this->getController()->getActionStack()->getLastEntry()->getActionInstance();

    $queryParams = $this->getUser()->getLocalPrefs()
      ->syncRequestParams('mystorieslist', [
        uiSelectPager::QUERY_ROWSPERPAGE => 10
      ]);

    // order by
    $order_by = [
      'seq_nr'   => 'seq_nr ASC',
      'keyword'  => 'keyword ASC',
      'lastedit' => 'updated_on DESC',
      'votes'    => 'stars DESC',
      'reports'  => 'kicks DESC',
      'public'   => 'public DESC'
    ];

    $sortkey = $request->getParameter('sort', 'lastedit');

    if ($isProfile) {
      $sortkey = 'seq_nr'; // fixed
    }

    $action->forward404Unless(!$sortkey || preg_match('/^[a-z_]+$/', $sortkey));
    $orderClause = isset($order_by[$sortkey]) ? $order_by[$sortkey] : $order_by['seq_nr'];

    // Stories select
    $storiesSelect = StoriesPeer::getMyStoriesSelect($userId);

    // filter out private stories
    if (!$isSelf || $isProfile || $sortkey === 'public')
    {
      $storiesSelect->where('public = 1');
    }

    // pager
    $this->pager = new uiSelectPager([
      'select'       => $storiesSelect,
      'internal_uri' => 'study/mystories',
      'query_params' => [
        uiSelectTable::QUERY_SORTCOLUMN => $request->getParameter(uiSelectTable::QUERY_SORTCOLUMN, 'seq_nr'),
        uiSelectTable::QUERY_SORTORDER  => $request->getParameter(uiSelectTable::QUERY_SORTORDER, 1),
        uiSelectPager::QUERY_ROWSPERPAGE => $queryParams[uiSelectPager::QUERY_ROWSPERPAGE]
      ],
      'max_per_page' => $queryParams[uiSelectPager::QUERY_ROWSPERPAGE],
      'page'         => $request->getParameter(uiSelectPager::QUERY_PAGENUM, 1)
    ]);
    $this->pager->init();

    // get row data
    $get_select = clone($this->pager->getSelect());
    $get_select->order($orderClause);
    $rows = sfProjectConfiguration::getActive()->getDatabase()->fetchAll($get_select);
//DBG::out($get_select);
    foreach ($rows as &$R)
    {
      // public/private icon
      $R['share'] = $R['public'] == 1;

      $R['story'] = StoriesPeer::getFormattedStory($R['story'], $R['keyword'], false);
    }

    $this->rows = $rows;

    return sfView::SUCCESS;
  }
}

