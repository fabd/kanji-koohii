<?php
class MembersListComponent extends sfComponent
{
  public function execute($request)
  {
    $queryParams = $this->getUser()->getLocalPrefs()
      ->syncRequestParams('memberslist', array(
        uiSelectPager::QUERY_ROWSPERPAGE => 50,
        uiSelectTable::QUERY_SORTCOLUMN  => 'ts_lastreview',
        uiSelectTable::QUERY_SORTORDER   => 1
      ));

    // pager
    $this->pager = new uiSelectPager(array
    (
      'select'       => ActiveMembersPeer::getSelectForActiveMembersList(),
      'internal_uri' => '@members_list',
      'query_params' => $queryParams,
      'max_per_page' => $queryParams[uiSelectPager::QUERY_ROWSPERPAGE],
      'page'         => $request->getParameter(uiSelectPager::QUERY_PAGENUM, 1)
    ));
    $this->pager->init();
    
    // data table
    $this->table = new uiSelectTable(new MembersListBinding(), $this->pager->getSelect(), $request->getParameterHolder());

    return sfView::SUCCESS;
  }
}

/**
 * Kanji Review Summary
 * 
 */
class MembersListBinding implements uiSelectTableBinding
{
  public function getConfig()
  {
    // MUST BE VALID JSON! ! !
    return <<< EOD
    {
      "settings": {
        "primaryKey": ["userid"],
        "sortColumn":  "ts_lastreview",
        "sortOrder":  1
      },
      "columns": [
        {
          "caption":   "Member",
          "width":     28,
          "colData":  "username",
          "colDisplay": "_username"
        },
        {
          "caption":   "From",
          "width":     33,
          "colData":  "location"
        },
        {
          "caption":   "Last review",
          "width":     25,
          "cssClass": "center",
          "colData":  "ts_lastreview",
          "colDisplay":"_lastreview"
        },
        {
          "caption":   "Flashcards",
          "width":     10,
          "cssClass": "right",
          "colData":  "fc_count"
        }
      ]
    }
EOD;
  }

  public function filterDisplayData(uiSelectTableRow $row)
  {
    $rowData =& $row->getRowData();

    // needs MemberHelper in the view template
    $rowData['_username'] = link_to_member($rowData['username']);

    $rowData['_lastreview'] = simple_format_date((int)$rowData['ts_lastreview'], rtkLocale::DATE_SHORT);

    return $rowData;
  }
  
  public function validateRowData(array $rowData)
  {
  }
  
  public function saveRowData(array $rowData, $newrow = false)
  {
  }
  
  public function deleteRow(array $row_ids)
  {
  }
}
