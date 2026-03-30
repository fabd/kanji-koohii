<?php
/**
 * Flashcards actions.
 * 
 * Actions:
 *   executeAdd()
 *   executeEdit()
 * 
 */

class flashcardsActions extends sfActions
{
  public function executeIndex()
  {
    // for testing the HTTP 500 error page (nothing should link here)
    throw new sfException("Woopsies");
  }

  public function executeAdd($request)
  {
    $json = $request->getContentJson();
    $ucsId = BaseValidators::sanitizeInteger($json->ucs);

    $userId = $this->getUser()->getUserId();
    $status = JsTron::STATUS_FAILED;

    if (!ReviewsPeer::hasFlashcard($userId, $ucsId))
    {
      $added = ReviewsPeer::addSelection($userId, [$ucsId]);

      if (count($added) === 1)
      {
        $status = JsTron::STATUS_SUCCESS;
      }
    }

    $tron = new JsTron();
    $tron->setStatus($status);

    return $tron->renderJson($this);
  }

  /**
   * Used by the Flashcard Edit Dialog.
   *
   * GET params
   *   ucs      UCS-2 code value of the character.
   * 
   * POST params
   *   ucs
   *   action
   * 
   * @return void
   */
  public function executeEdit(sfRequest $request)
  {
    $userId = $this->getUser()->getUserId();

    // FUUUU legacy YUI2 Connect VS API post using JSON
    if ($request->hasParameter('ucs'))  {
      $json = (object) ['ucs' => $request->getParameter('ucs')];
    }
    else {
      $json = $request->getContentJson();
    }

    $ucsId  = BaseValidators::sanitizeInteger($json->ucs);
    
    $tron = new JsTron();
    
    if ($request->getMethod()===sfRequest::GET)
    {
      // GET request when dialog opens
      $kanjiData = KanjisPeer::getKanjiByUCS($ucsId);
      $this->forward404If(!$kanjiData);
      $cardData = ReviewsPeer::getFlashcardData($userId, $ucsId);

      $tron->add([
        'kanjiData' => $kanjiData,
        'cardData' => $cardData,
      ]);
    }
    else {
      $action = $json->action;
      
      if ($action === "delete") {
        $result = ReviewsPeer::deleteSelection($userId, [$ucsId]);
        if (empty($result)) {
          $tron->setError("Error deleting flashcard.");
          $tron->setStatus(JsTron::STATUS_FAILED);
        }
      }

      if ($action === "restudy") {
        $result = ReviewsPeer::failFlashcard($userId, $ucsId);
        if (!$result) {
          $tron->setError("Woops. Could not update flashcard.");
          $tron->setStatus(JsTron::STATUS_FAILED);
        }
      }
    }

    return $tron->renderJson($this);
  }
}
