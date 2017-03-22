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

    $max_level = $request->getParameter('max_level', 0);
      
    if ($request->hasParameter('max_level'))
    {
      $this->forward404If($max_level < 1 || $max_level > rtkIndex::inst()->getNumCharacters(), 'Invalid card range');
    }

    $options['items'] = rtkLabs::iVocabShuffleBegin($max_level);
    $this->uiFR = new uiFlashcardReview($options, true);
    $this->reviewOptions = $options;
  }

  /**
   * Start iVocabShuffle flashcard review using learned kanji in the SRS!
   *
   */
  public function executeShuffle2($request)
  {
    $this->setLayout('fullscreenLayout');

    $options['items'] = rtkLabs::iVocabShuffleMagicBegin();

    $this->uiFR = new uiFlashcardReview($options, true);
    $this->reviewOptions = $options;
  }

  /**
   * iVocabShuffle ajax handler.
   * 
   * @see  uiFlashcardReview.php for POST request parameters.
   *
   */
  public function executeAjax($request)
  {
    $oJson = coreJson::decode($request->getParameter('json', '{}'));

    if (!empty($oJson))
    {
      $options = array(
        'fn_get_flashcard' => array('rtkLabs', 'getVocabShuffleCardData')
      );

      $flashcardReview = new uiFlashcardReview($options);
      $this->getResponse()->setContentType('application/json');
      return $this->renderText( $flashcardReview->handleJsonRequest($oJson) );
    }

    throw new rtkAjaxException('Empty JSON Request.');
  }
}
