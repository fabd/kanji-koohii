<?php
/**
 * Flashcards actions.
 * 
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
   * Returns body of the Flashcard Edit Dialog on the Study page.
   *
   * Displays flashcard stats, and menu options to add, delete, fail, and
   * review cards.
   *
   * Request parameters:
   *   ucs      UCS-2 code value of the character.
   *   menu     Option selected in the menu (menuid attribute of the clicked
   *            menu item) (OPTIONAL)
   * 
   * @return void
   */
  public function executeDialog($request)
  {
    $userId = $this->getUser()->getUserId();

    // character data
    $ucsId  = BaseValidators::sanitizeInteger($request->getParameter('ucs'));
    $charData = KanjisPeer::getKanjiByUCS($ucsId);
    $this->forward404If($charData === false);
    $extFrameNum = rtkIndex::getIndexForUCS($charData->ucs_id);
    
    // flashcard data, or false if flashcard does not exist
    $cardData = ReviewsPeer::getFlashcardData($userId, $ucsId);

    $result = '';                // result send back to the client
    $message = false;            // confirmation message, if any
    $confirm = false;            // if confirming an action, display Ok/Cancel and pass this id to menuitem as "data-action"
    $tron = new JsTron();
    $tron->add([
      'dialogTitle'   => 'Edit Flashcard'
    ]);
    $tron->setStatus(JsTron::STATUS_PROGRESS);
    
    sfProjectConfiguration::getActive()->loadHelpers('CJK');

    // handle menu action
    $menu = $request->getParameter('menu');
    if ($menu === 'fail' && $this->menuFlashcardFail($request, $userId, $ucsId))
    {
      $result  = 'failed';
      $message = 'Flashcard moved to the restudy pile. The page will reload.';
      // tell client to load this page
      $tron->set('reload', $this->getController()->genUrl('@study_edit?id='.$extFrameNum));
    }
    elseif ($menu === 'delete')
    {
      $message = 'Delete flashcard for '.cjk_lang_ja($charData->kanji).' (#'.$extFrameNum.') ?'.
                 '<span class="note">Note: only the flashcard is deleted, stories are not affected.</span>';
      $result  = true;     // just for state change
      $confirm = 'delete';
    }    
    elseif ($menu === 'confirm-delete' && $this->menuFlashcardDelete($request, $userId, $ucsId))
    {
      $result  = 'deleted';
      $message = 'Deleted flashcard for '.cjk_lang_ja($charData->kanji).' (#'.$extFrameNum.')';
    }

    if ($request->hasErrors())
    {
      // if an error occured, it is likely the state changed in another tab/window
      // set a non empty result to tell client to refresh the dialog state
      $result = 'error';
    }

    // data for the client
    $tron->set('result', $result);
//sleep( 3);
    return $tron->renderPartial($this, 'EditFlashcard', [
      'charData'     => $charData,
      'cardData'     => $cardData,
      'message'      => $message,
      'confirm'      => $confirm           
    ]);
  }

  private function menuFlashcardFail($request, $userId, $ucsId)
  {
    if (ReviewsPeer::hasFlashcard($userId, $ucsId))
    {
      if (false !== ReviewsPeer::failFlashcard($userId, $ucsId))
      {
        return true;
      }
    }

    // not expected to be seen by user
    $request->setError('x', __METHOD__);
    return false;
  }

  private function menuFlashcardDelete($request, $userId, $ucsId)
  {
    $deleted = ReviewsPeer::deleteSelection($userId, [$ucsId]);

    if (count($deleted) === 1)
    {
      return true;
    }

    // not expected to be seen by user
    $request->setError('x', __METHOD__);
    return false;
  }
}
