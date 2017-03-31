<?php
/**
 * Edit Keywords Table (Manage Flashcards)
 * 
 */

class EditKeywordsTableComponent extends sfComponent
{
  public function execute($request)
  {
    $queryParams = $this->getUser()->getLocalPrefs()
      ->syncRequestParams('manage.removelist', array(
        uiSelectPager::QUERY_ROWSPERPAGE => 20,
        uiSelectTable::QUERY_SORTCOLUMN  => 'seq_nr',
        uiSelectTable::QUERY_SORTORDER   => 0
      ));
    
    // pager
    $this->pager = new uiSelectPager(array
    (
      'select'       => ReviewsPeer::getSelectForEditKeywordsList($this->getUser()->getUserId()),
      'internal_uri' => 'manage/EditKeywordsTable',
      'query_params' => $queryParams,
      'max_per_page' => $queryParams[uiSelectPager::QUERY_ROWSPERPAGE],
      'page'         => $request->getParameter(uiSelectPager::QUERY_PAGENUM, 1)
    ));
    $this->pager->init();
    
    // data table
    $binding = new EditKeywordsTableBinding();
    $this->table = new uiSelectTable($binding, $this->pager->getSelect(), $request->getParameterHolder());
    $this->table->configure(array(
      'sortColumn' => $queryParams[uiSelectTable::QUERY_SORTCOLUMN],
      'sortOrder'  => $queryParams[uiSelectTable::QUERY_SORTORDER]
    ));

  }
}

/**
 * Remove flashcards selection table, checkboxes allow to select flashcards to remove from deck.
 * 
 */
class EditKeywordsTableBinding implements uiSelectTableBinding
{
  protected
    $_selection = null;
  
  public function getConfig()
  {
    sfProjectConfiguration::getActive()->loadHelpers(array('Asset', 'SimpleDate', 'CJK'));
    
    // MUST BE VALID JSON! ! !
    return <<< EOD
    {
      "settings": {
        "primaryKey": ["ucs_id"]
      },
      "columns": [
        {
          "caption":   "Index",
          "width":     5,
          "cssClass":  "center",
          "colData":  "seq_nr"
        },
        {
          "caption":   "Char.",
          "width":     7,
          "cssClass":  "kanji",
          "colData":  "kanji",
          "colDisplay":  "_kanji"
        },
        {
          "caption":   "Heisig&nbsp;Keyword",
          "width":     20,
          "cssClass":  "keyword",
          "colData":  "keyword",
          "colDisplay":  "_keyword"
        },
        {
          "caption":   "Customized&nbsp;Keyword",
          "width":     20,
          "cssClass":  "keyword JSCkwTd",
          "colData":   "custkeyword",
          "colDisplay":  "_custkeyword"
        },
        {
          "caption":   "Edit",
          "width":     5,
          "cssClass":  "center edit-keyword",
          "colDisplay":   "_edit"
        }
        
      ]
    }
EOD;
  }

  public function filterDisplayData(uiSelectTableRow $row)
  {
    $rowData =& $row->getRowData();

    $rowData['_kanji'] = cjk_lang_ja($rowData['kanji']);

    $rowData['_keyword'] = str_replace('/', '/<br/>', $rowData['keyword']);

    // create edit keyword link
    $rowData['_custkeyword'] = esc_specialchars($rowData['custkeyword']); //'<span class="JSEditKeyword" data-url="'.$url.'">'.$rowData['custkeyword'].'</span>';

    $rowData['_edit'] = image_tag('/images/ui/icons/pencil.png', array(
      'size'     => '16x16',
      'class'    => 'edit-icon JSEditKeyword',
      'data-id'  => $rowData['ucs_id']
    ));
    
    //$tsLastReview = (int)$rowData['ts_lastreview'];
    //$rowData['_lastreview'] = $tsLastReview ? simple_format_date($tsLastReview, rtkLocale::DATE_SHORT) : '-';
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
