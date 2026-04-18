<?php

/**
 * @property array $reviewOptions
 */
class labsActions extends sfActions
{
  public function executeIndex(coreRequest $request) {}

  /**
   * Start iVocabShuffle flashcard review using Heisig #.
   */
  public function executeShuffle1(coreRequest $request)
  {
    $this->setLayout('fullscreenLayout');

    $max_framenum = (int) $request->getParameter('max_framenum');

    if ($request->hasParameter('max_framenum')) {
      $this->forward404If($max_framenum < 1 || $max_framenum > rtkIndex::inst()->getNumCharacters(), 'Invalid card range');
    }

    $reviewOptions['items']    = rtkLabs::getVocabShuffleMode1Items($max_framenum);
    $reviewOptions['ajax_url'] = $this->getController()->genUrl('labs/ajax');
    $reviewOptions['exit_url'] = 'review/vocab';

    // these will be variables in the review template partial
    $this->reviewOptions = $reviewOptions;

    FlashcardReview::getInstance()->start();
  }

  /**
   * Start iVocabShuffle flashcard review using learned kanji in the SRS!
   */
  public function executeShuffle2(coreRequest $request)
  {
    $this->setLayout('fullscreenLayout');

    $reviewOptions['items']    = rtkLabs::getVocabShuffleMode2Items();
    $reviewOptions['ajax_url'] = $this->getController()->genUrl('labs/ajax');
    $reviewOptions['exit_url'] = 'review/vocab';

    // these will be variables in the review template partial
    $this->reviewOptions = $reviewOptions;

    FlashcardReview::getInstance()->start();
  }

  /**
   * iVocabShuffle ajax handler.
   *
   * @see  FlashcardReview.php for POST request parameters.
   */
  public function executeAjax(coreRequest $request)
  {
    $fcrData = $request->getContentJson();

    $flashcardReview = FlashcardReview::getInstance()->config([
      'fn_get_flashcard' => ['rtkLabs', 'getVocabShuffleCardData'],
    ]);

    return $this->renderJson($flashcardReview->handleRequest($fcrData));
  }
}
