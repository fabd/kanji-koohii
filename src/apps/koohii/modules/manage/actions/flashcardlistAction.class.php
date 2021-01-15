<?php
class flashcardlistAction extends sfAction
{
  public function execute($request)
  {
    $queryParams = $this->getUser()->getLocalPrefs()
      ->syncRequestParams('detailedflashcardlist', [
        uiSelectPager::QUERY_ROWSPERPAGE => 20,
        uiSelectTable::QUERY_SORTCOLUMN  => 'seq_nr',
        uiSelectTable::QUERY_SORTORDER   => 0
      ]);

    $this->pager = new uiSelectPager([
      'select'       => ReviewsPeer::getSelectForDetailedList($this->getUser()->getUserId()),
      'internal_uri' => 'manage/flashcardlist',
      'query_params' => $queryParams,
      'max_per_page' => $queryParams[uiSelectPager::QUERY_ROWSPERPAGE],
      'page'         => $request->getParameter(uiSelectPager::QUERY_PAGENUM, 1)
    ]);
    $this->pager->init();
    
    $this->table = new uiSelectTable(new FlashcardListBinding(), $this->pager->getSelect(), $request->getParameterHolder());
    $this->table->configure([
      'sortColumn' => $queryParams[uiSelectTable::QUERY_SORTCOLUMN],
      'sortOrder'  => $queryParams[uiSelectTable::QUERY_SORTORDER]
    ]);
    
  }
}

/**
 * Detailed Flashcard List
 * 
 */
class FlashcardListBinding implements uiSelectTableBinding
{
  public function getConfig()
  {
    sfProjectConfiguration::getActive()->loadHelpers(['CJK']);
  
    // MUST BE VALID JSON! ! !
    return <<< EOD
    {
      "settings": {
        "primaryKey": ["seq_nr"]
      },
      "columns": [
        {
          "caption":   "#",
          "width":     5,
          "cssClass":  "text-center",
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
          "caption":   "Keyword",
          "width":     19,
          "cssClass":  "keyword",
          "colData":  "keyword",
          "colDisplay":  "_keyword"
        },
        {
          "caption":   "Onyomi",
          "width":     15,
          "cssClass":  "whitespace-nowrap",
          "colData":  "onyomi"
        },
        {
          "caption":  "<span class=\"visible-xs-sm\">S</span><span class=\"visible-md-lg\">Strokecount</span>",
          "width":    15,
          "cssClass": "text-center",
          "colData":  "strokecount"
        },
        
        {
          "caption":   "Pass",
          "width":     8,
          "cssClass":  "font-bold text-center",
          "colData":  "successcount"
        },
        {
          "caption":   "Fail",
          "width":     8,
          "cssClass":  "text-center red",
          "colData":  "failurecount"
        },
        {
          "caption":   "Box",
          "width":     8,
          "cssClass":  "font-bold text-center",
          "colData":  "leitnerbox"
        },
        {
          "caption":   "Last&nbsp;Review",
          "width":     15,
          "cssClass":  "text-center",
          "colData":  "ts_lastreview",
          "colDisplay":"_lastreview"
        }
      ]
    }
EOD;
  }

  public function filterDisplayData(uiSelectTableRow $row)
  {
    $rowData =& $row->getRowData();

    if ($rowData['failurecount']==0)
    {
      $rowData['failurecount'] = '';
    }
    $rowData['_kanji'] = cjk_lang_ja($rowData['kanji']);
    $rowData['_keyword'] = link_to_keyword($rowData['keyword'], $rowData['seq_nr']);
    
    $lastReviewTS = (int)$rowData['ts_lastreview'];
    
    $rowData['_lastreview'] = $lastReviewTS ? simple_format_date($lastReviewTS, rtkLocale::DATE_SHORT) : '-';
    
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
