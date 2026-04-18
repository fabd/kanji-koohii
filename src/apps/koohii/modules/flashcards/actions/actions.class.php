<?php
/**
 * Flashcards actions.
 *
 * Actions:
 *   executeAdd()
 *   executeEdit()
 */
class flashcardsActions extends sfActions
{
  public function executeIndex(coreRequest $request)
  {
    // for testing the HTTP 500 error page (nothing should link here)
    throw new sfException('Woopsies');
  }

  public function executeAdd(coreRequest $request)
  {
    $json  = $request->getContentJson();
    $ucsId = BaseValidators::sanitizeInteger($json->ucs);

    $userId = kk_get_user()->getUserId();
    $status = JsTron::STATUS_FAILED;

    if (!ReviewsPeer::hasFlashcard($userId, $ucsId)) {
      $added = ReviewsPeer::addSelection($userId, [$ucsId]);

      if (count($added) === 1) {
        $status = JsTron::STATUS_SUCCESS;
      }
    }

    $tron = new JsTron();
    $tron->setStatus($status);

    return $this->renderJson($tron);
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
   */
  public function executeEdit(coreRequest $request)
  {
    $userId = kk_get_user()->getUserId();

    // the GET request is not JSON
    if ($request->hasParameter('ucs')) {
      $json = (object) ['ucs' => $request->getParameter('ucs')];
    } else {
      $json = $request->getContentJson();
    }

    $ucsId = BaseValidators::sanitizeInteger($json->ucs);

    $tron = new JsTron();

    if ($request->getMethod() === sfRequest::GET) {
      // GET request when dialog opens
      $kanjiData = KanjisPeer::getKanjiByUCS($ucsId);
      $this->forward404If(!$kanjiData);
      $cardData = ReviewsPeer::getFlashcardData($userId, $ucsId);

      $tron->add([
        'kanjiData' => $kanjiData,
        'cardData'  => $cardData,
      ]);
    } else {
      $action = $json->action;

      if ($action === 'delete') {
        $result = ReviewsPeer::deleteSelection($userId, [$ucsId]);
        if (empty($result)) {
          $tron->addError('Error deleting flashcard.');
          $tron->setStatus(JsTron::STATUS_FAILED);
        }
      }

      if ($action === 'restudy') {
        $result = ReviewsPeer::failFlashcard($userId, $ucsId);
        if (!$result) {
          $tron->addError('Woops. Could not update flashcard.');
          $tron->setStatus(JsTron::STATUS_FAILED);
        }
      }
    }

    return $this->renderJson($tron);
  }
}
