<?php
class labsActions extends sfActions
{
  public function executeIndex($request)
  {
  }
  
  /**
   * Start iVocabShuffle flashcard review using Heisig #.
   *
   */
  public function executeShuffle1($request)
  {
    $this->setLayout('fullscreenLayout');

    $max_framenum = $request->getParameter('max_framenum', 0);
      
    if ($request->hasParameter('max_framenum'))
    {
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
   *
   */
  public function executeShuffle2($request)
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
   *
   */
  public function executeAjax($request)
  {
    $fcrData = json_decode($request->getParameter('json', '{}'));

    if (empty($fcrData)) {
      throw new rtkAjaxException('Empty JSON Request.');
    }

    $flashcardReview = FlashcardReview::getInstance()->config([
      'fn_get_flashcard' => ['rtkLabs', 'getVocabShuffleCardData']
    ]);

    return $this->renderJson($flashcardReview->handleRequest($fcrData));
  }
}
