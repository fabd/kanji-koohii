<?php
/**
 * Provides a simple grid/list view for the free review mode.
 *
 * @author  Fabrice Denis
 */
class summarySimpleComponent extends sfComponent
{
  /**
   * @param object $request
   */
  public function execute($request)
  {
    $oFRS = new rtkFreeReviewSession();
    $answers = $oFRS->getReviewedCards();

    $this->cards = [];

    if (count($answers))
    {
      $keywords = CustkeywordsPeer::getCustomKeywords($this->getUser()->getUserId());

      foreach ($answers as $ucsId => $iAnswer)
      {
        // FIXME : free mode should not handle ratings other than YES/NO/AGAIN
        //         (fix keyboard shortcuts)
        $isPass = in_array($iAnswer, [
          uiFlashcardReview::RATE_HARD,
          uiFlashcardReview::RATE_YES,
          uiFlashcardReview::RATE_EASY,
        ]);

        $card = [
          'kanji' => utf8::fromUnicode($ucsId),
          'framenum' => rtkIndex::getIndexForUCS($ucsId),
          'keyword' => $keywords[$ucsId]['keyword'],
          'pass' => $isPass,
        ];

        $this->cards[] = (object) $card;
      }
    }

    return sfView::SUCCESS;
  }
}
