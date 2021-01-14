<?php
/**
 * Kanji Flashcard Review Summary
 * 
 * Display a list of the flashcards from the last review session,
 * and a graphi indicating the ratio of remembered vs forgotten cards.
 *
 * POST Parameters (see _ReviewKanjiView.php):
 *
 *   fc_pass
 *   fc_fail
 *   fc_deld
 *   fc_free   1 if Free Review Mode
 *
 * If Free Review Mode, additional POST parameters:
 *
 *   fc_rept   URL to use for Repeat button.
 * 
 */

class summaryAction extends sfAction
{
  public function execute($request)
  {
    // free mode review flag
    $this->fc_free = $request->getParameter('fc_free', 0);
    $this->fc_rept = $request->getParameter('fc_rept', '');
    
    if ($request->getMethod()===sfRequest::POST)
    {
      // validate post parameters and save user's review session info
      $validator = new coreValidator($this->getContext()->getActionName());
      $this->forward404Unless($validator->validate($request->getParameterHolder()->getAll()));
      
      if (!$this->fc_free)
      {
        // clear remaining items from the Learned list so user can Restudy again
        LearnedKanjiPeer::clearAll($this->getUser()->getUserId());  

        // save this session review info for later GET requests
        $this->saveReviewSessionInfo($request->getParameterHolder());

        // update last review timestamp and total reviews count
        ActiveMembersPeer::updateFlashcardInfo($this->getUser()->getUserId());
      }
    }
    else
    {
      // grab the user's most recent review session info from db
      $params = ActiveMembersPeer::getReviewSummaryInfo($this->getUser()->getUserId());
      $this->forward404Unless($params);

      // add request parameters!
      $request->getParameterHolder()->add($params);
    }

    // template vars
    $this->ts_start = $request->getParameter('ts_start', 0);
    $this->numRemembered = (int) $request->getParameter('fc_pass', 0);
    $this->numForgotten = (int) $request->getParameter('fc_fail', 0);
    $this->numTotal = $this->numRemembered + $this->numForgotten;
    
    if ($this->numRemembered === $this->numTotal) {
      $this->title = 'Hurrah! All remembered!';
    }
    elseif ($this->numForgotten === $this->numTotal && $this->numTotal > 1) {
      $this->title = 'Eek! All forgotten!';
    }
    else {
      $this->title = 'Remembered '.$this->numRemembered.' of '.$this->numTotal.' '._CJ('kanji').'.';
    }

    // deleted cards
    $deletedCards = $request->getParameter('fc_deld', '');
    $this->deletedCards = $deletedCards ? explode(',', $deletedCards) : [];
  }
  
  /**
   * Save information from the last flashcard review session in the database.
   * Allows to see the last review session results on subsequent GET request,
   * and until the user starts another review session.
   * 
   * The data could also be used in the active member statistics, ..
   * 
   * @param object $params
   */
  protected function saveReviewSessionInfo(sfParameterHolder $params)
  {
      $data = [
        'ts_start' => $params->get('ts_start'),
        'fc_pass'  => $params->get('fc_pass'),
        'fc_fail'  => $params->get('fc_fail')
      ];
      ActiveMembersPeer::saveReviewSummaryInfo($this->getUser()->getUserId(), $data);
  }
}
