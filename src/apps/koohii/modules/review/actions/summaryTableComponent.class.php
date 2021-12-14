<?php

const RATING_LABELS = [
  '' => '-',
  FlashcardReview::RATE_NO => 'No',
  FlashcardReview::RATE_HARD => 'Hard',
  FlashcardReview::RATE_YES => 'Yes',
  FlashcardReview::RATE_EASY => 'Easy',
  FlashcardReview::RATE_DELETE => 'Delete',
  FlashcardReview::RATE_SKIP => '(skipped)',
  FlashcardReview::RATE_AGAIN => 'Again',
  FlashcardReview::RATE_AGAIN_HARD => 'Again > Hard',
  FlashcardReview::RATE_AGAIN_YES => 'Again > Yes',
  FlashcardReview::RATE_AGAIN_EASY => 'Again > Easy',
];

class summaryTableComponent extends sfComponent
{
  /**
   * Component variables:.
   *
   *   ts_start   Timestamp from the review session start time
   *
   * @param object $request
   */
  public function execute($request)
  {
    $queryParams = $this->getUser()->getLocalPrefs()
      ->syncRequestParams('reviewsummary', [
        uiSelectPager::QUERY_ROWSPERPAGE => 20,
        uiSelectTable::QUERY_SORTCOLUMN => 'seq_nr',
        uiSelectTable::QUERY_SORTORDER => 0,
      ])
    ;

    // pager
    $this->pager = new uiSelectPager([
      'select' => ReviewsPeer::getReviewSummaryListSelect($this->getUser()->getUserId(), $this->ts_start),
      'internal_uri' => '@review_summary',
      'query_params' => $queryParams,
      'max_per_page' => $queryParams[uiSelectPager::QUERY_ROWSPERPAGE],
      'page' => $request->getParameter(uiSelectPager::QUERY_PAGENUM, 1),
    ]);
    $this->pager->init();

    // data table
    $this->table = new uiSelectTable(new FlashcardListBinding(), $this->pager->getSelect(), $request->getParameterHolder());

    // fixes duplicates between pages, when sorting by non-unique col
    $this->table->configure(['sortColumnTwo' => 'seq_nr']);

    return sfView::SUCCESS;
  }
}

/**
 * Kanji Review Summary.
 */
class FlashcardListBinding implements uiSelectTableBinding
{
  private $ratings;

  public function getConfig()
  {
    $this->ratings = FlashcardReview::getInstance()->getCachedAnswers();

    // MUST BE VALID JSON! ! !
    return <<<EOD
      {
        "settings": {
          "primaryKey": ["seq_nr"],
          "sortColumn":  "seq_nr",
          "sortOrder":  0
        },
        "columns": [
          {
            "caption":   "Framenum",
            "width":     5,
            "cssClass":  "text-center",
            "colData":  "seq_nr"
          },
          {
            "caption":   "Char.",
            "width":     7,
            "cssClass":  "kanji",
            "colData":  "kanji",
            "colDisplay": "_kanji"
          },
          {
            "caption":   "Keyword",
            "width":     19,
            "cssClass":  "keyword left",
            "colData":  "keyword",
            "colDisplay":  "keyword"
          },
          {
            "caption":   "Onyomi",
            "width":     15,
            "cssClass":  "nowrap",
            "colData":  "onyomi"
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
            "caption":   "Review Rating",
            "width":     15,
            "cssClass":  "",
            "colDisplay":  "_rating"
          }
        ]
      }
      EOD;
  }

  public function filterDisplayData(uiSelectTableRow $row)
  {
    $rowData = &$row->getRowData();

    $rowData['failurecount'] = empty($rowData['failurecount']) ? '' : $rowData['failurecount'];
    $rowData['_kanji'] = cjk_lang_ja($rowData['kanji']);
    $rowData['keyword'] = link_to_keyword($rowData['keyword'], $rowData['seq_nr']);

    $ucsId = (int) $rowData['ucs_id'];
    $ratingId = $this->ratings[$ucsId] ?? '';
    $ratingStr = RATING_LABELS[$ratingId];
    $rowData['_rating'] = <<<EOD
      <span class="SummaryTableRating is-{$ratingId}">{$ratingStr}</span>
      EOD;
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
