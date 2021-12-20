<?php
/**
 * Kanji Flashcard Review Summary.
 *
 * Display a list of the flashcards from the last review session,
 * and a graphi indicating the ratio of remembered vs forgotten cards.
 *
 * POST Parameters (see _ReviewKanjiView.php):
 *
 *   fc_deld
 *   fc_free   1 if Free Review Mode
 *
 * If Free Review Mode, additional POST parameters:
 *
 *   fc_rept   URL to use for Repeat button.
 */
class summaryAction extends sfAction
{
  public function execute($request)
  {
    // free mode review flag
    $this->fc_free = $request->getParameter('fc_free', 0);
    $this->fc_rept = $request->getParameter('fc_rept', '');

    // POST request is initiated by the end of a review session
    if ($request->getMethod() === sfRequest::POST)
    {
      // validate post parameters and save user's review session info
      $validator = new coreValidator($this->getContext()->getActionName());
      $this->forward404Unless($validator->validate($request->getParameterHolder()->getAll()));

      if (!$this->fc_free)
      {
        // clear remaining items from the Learned list so user can Restudy again
        LearnedKanjiPeer::clearAll($this->getUser()->getUserId());

        // update last review timestamp and total reviews count
        ActiveMembersPeer::updateFlashcardInfo($this->getUser()->getUserId());
      }
    }
  }
}
