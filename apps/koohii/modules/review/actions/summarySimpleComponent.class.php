<?php
/**
 * Provides a simple grid/list view for the free review mode.
 * 
 * 
 * @author  Fabrice Denis
 */

class summarySimpleComponent extends sfComponent
{
  /**
   * 
   * @param object $request
   */
  public function execute($request)
  {
    $oFRS = new rtkFreeReviewSession();
    $answers = $oFRS->getReviewedCards();

    $this->cards = array();

    if (count($answers))
    {
      $keywords = CustkeywordsPeer::getCustomKeywords($this->getUser()->getUserId());

      foreach ($answers as $ucsId => $iAnswer) {
        $card = array(
          'kanji'    => utf8::fromUnicode($ucsId),
          'framenum' => rtkIndex::getIndexForUCS($ucsId),
          'keyword'  => $keywords[$ucsId]['keyword'],
          'pass'     => $iAnswer !== uiFlashcardReview::UIFR_NO   /* Yes or Easy = pass */
        );

        $this->cards[] = (object) $card;
      }
    }

    return sfView::SUCCESS;
  }
}
