<?php

class summarySimpleComponent extends sfComponent
{
  /**
   * @param object $request
   */
  public function execute($request)
  {
    $answers = uiFlashcardReview::getInstance()->getCachedAnswers();

    $this->cards = [];

    if (count($answers))
    {
      $keywords = CustkeywordsPeer::getCustomKeywords($this->getUser()->getUserId());

      foreach ($answers as $ucsId => $answer)
      {
        // FIXME : free mode should not handle ratings other than YES/NO/AGAIN
        //         (fix keyboard shortcuts)
        $isPass = in_array($answer, [
          uiFlashcardReview::RATE_HARD,
          uiFlashcardReview::RATE_YES,
          uiFlashcardReview::RATE_EASY,
          uiFlashcardReview::RATE_AGAIN_HARD,
          uiFlashcardReview::RATE_AGAIN_YES,
          uiFlashcardReview::RATE_AGAIN_EASY,
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
