<?php

/**
 * @property array{ucsId: int, pass: bool}[] $cards
 */
class summarySimpleComponent extends sfComponent
{
  public function execute($request): string
  {
    $answers = FlashcardReview::getInstance()->getCachedAnswers();

    // ------ sample data for debugging
    // echo json_encode($answers, JSON_PRETTY_PRINT);
    $DATA = <<<EOD
{ "20013": "yes", "30334": "yes", "33258": "yes", "20024": "yes", "21315": "yes", "30333": "yes", "32920": "no", "21331": "no", "26397": "no", "19979": "no", "26119": "no", "21344": "no", "21319": "no", "19978": "no", "21338": "no" }
EOD;
    $answers = json_decode($DATA, true);
    // ------

    $this->cards = [];

    if (count($answers)) {
      foreach ($answers as $ucsId => $answer) {
        // FIXME : free mode should not handle ratings other than YES/NO/AGAIN
        //         (fix keyboard shortcuts)
        $isPass = in_array($answer, [
          LeitnerSRS::RATE_HARD,
          LeitnerSRS::RATE_YES,
          LeitnerSRS::RATE_EASY,
          LeitnerSRS::RATE_AGAIN_HARD,
          LeitnerSRS::RATE_AGAIN_YES,
          LeitnerSRS::RATE_AGAIN_EASY,
        ]);

        $this->cards[] = ['ucsId' => (int) $ucsId, 'pass' => $isPass];
      }

      // include orig & user keyword maps for the kanji card component
      rtkIndex::useKeywordsFile();

      $ucsIds = array_column($this->cards, 'ucsId');

      kk_globals_put([
        'USER_KEYWORDS_MAP'    => CustkeywordsPeer::getUserKeywordsMapJS(kk_get_user()->getUserId(), $ucsIds),
        'REVIEW_SUMMARY_CARDS' => $this->cards,
      ]);
    }

    return sfView::SUCCESS;
  }
}
