<?php
/**
 * Crude way of editing a flashcard, undocumented in site navigation.
 * 
 */

class advancedAction extends sfAction
{
  public function execute($request)
  {
    $MAX_STACKS = 50;

    $this->status = '';

    $userId = $this->getUser()->getUserId();
    $ucsId  = 0;

    if ($request->hasParameter('commit'))
    {
      // validate
      $kanji = $request->getParameter('f_kanji', '');
      if (!CJK::isKanjiChar($kanji) || false === ($kanji_data = KanjisPeer::getKanjiByCharacter($kanji)))
      {
        $request->setError('kanji', sprintf('Invalid kanji character. Must be a single character within CJK UNIFIED unicode.'));
        return;
      }
      $ucsId = (int)$kanji_data->ucs_id;

      $leitnerbox = $request->getParameter('f_leitnerbox');
      if (!BaseValidators::validateInteger($leitnerbox) || $leitnerbox < 1 || $leitnerbox > $MAX_STACKS) {
        $request->setError('box', sprintf('Leitnerbox must be between 1 and %s', $MAX_STACKS));
        return;
      }

      $expiredays = $request->getParameter('f_expiredays');
      if (!BaseValidators::validateInteger($expiredays) || $expiredays < 0 || $expiredays > 1000) {
        $request->setError('due', sprintf('Due date (days from now) must be between 0 and 1000'));
        return;
      }

      $failurecount = $request->getParameter('f_failurecount');
      if (!BaseValidators::validateInteger($failurecount) || $failurecount < 0 || $failurecount > 1000) {
        $request->setError('due', sprintf('Failure count must be between 0 and 1000'));
        return;
      }

      $successcount = $request->getParameter('f_successcount');
      if (!BaseValidators::validateInteger($successcount) || $successcount < 0 || $successcount > 10000) {
        $request->setError('due', sprintf('Success count must be between 0 and 10000'));
        return;
      }

      if ( $leitnerbox > 1 && ($successcount + 1 < $leitnerbox) ) {
        $request->setError('scb', sprintf('Success count must be min. (leitnerbox - 1)'));
        return;
      }


      $cardData = [
        'leitnerbox'   => $leitnerbox,
        'interval_days'=> $expiredays,
        'totalreviews' => $failurecount + $successcount,
        'failurecount' => $failurecount,
        'successcount' => $successcount
      ];

      if (!ReviewsPeer::hasFlashcard($userId, $ucsId)) {
        $cards = ReviewsPeer::addSelection($userId, [$ucsId]);
        if (count($cards) !== 1) {
          $request->setError('create', 'Error while creating flashcard.');
          return false;
        }
      }

      if (false === ReviewsPeer::updateFlashcard($userId, $ucsId, $cardData)) {
        $request->setError('update', 'Error while updating flashcard.');
        return false;
      }

      $this->cardInfo = ReviewsPeer::getFlashcardData($userId, $ucsId);
      unset($this->cardInfo->userid);
      unset($this->cardInfo->ts_lastreview);

      $this->status = 'success';
    }
  }
}

