<?php

class summarySimpleComponent extends sfComponent
{
  /**
   * @param object $request
   */
  public function execute($request)
  {
    $answers = FlashcardReview::getInstance()->getCachedAnswers();

    $this->cards = [];

    if (count($answers))
    {
      $keywords = CustkeywordsPeer::getCustomKeywords($this->getUser()->getUserId());

      foreach ($answers as $ucsId => $answer)
      {
        // FIXME : free mode should not handle ratings other than YES/NO/AGAIN
        //         (fix keyboard shortcuts)
        $isPass = in_array($answer, [
          FlashcardReview::RATE_HARD,
          FlashcardReview::RATE_YES,
          FlashcardReview::RATE_EASY,
          FlashcardReview::RATE_AGAIN_HARD,
          FlashcardReview::RATE_AGAIN_YES,
          FlashcardReview::RATE_AGAIN_EASY,
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
