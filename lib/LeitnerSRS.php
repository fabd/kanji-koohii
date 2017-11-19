<?php
/**
 * LeitnerSRS handles the flashcard scheduling system.
 * 
 * This class needs to be refactored to remove dependencies, so it can be included
 * in Trinity etc as a "spaced repetition engine".
 * 
 * Methods:
 * 
 *  rateCard($curData, $answer)
 *   
 * 
 * 
 * @author  Fabrice Denis
 */

class LeitnerSRS
{
  const    MAXSTACKS   = 8;
  const    FAILEDSTACK = 1;

  static
    // Leitner base intervals for flashcard going in box N
    // Offset 0 = Leitner box 1 (box 1 = failed/untested, box 2 = 1 review, ...)
    $SCHEDULE_DAYS     = array(0, 3, 7, 14, 30, 60, 120, 240),

    // Variance +/- for base interval for flashcard going in box N
    // Offset 0 = Leitner box 1 (box 1 = failed/untested, box 2 = 1 review, ...)
    $SCHEDULE_VARIANCE = array(0, 1, 2,  3,  5, 10,  15,  30);
  
  /**
   * Rate a flashcard, and update its review status accordingly.
   * 
   * Required input values:
   * 
   *   totalreviews
   *   leitnerbox
   *   failurecount
   *   successcount
   *   lastreview
   *   
   * Returns an array with only the values that have changed, to be saved
   * in the flashcard reviews storage.
   * 
   *   expiredate
   * 
   * @param  Object  $curData  Row data coming from flashcard review storage
   * @param  Integer $answer   Answer (see uiFlashcardReview.php const)
   * 
   * @return Array   Row data to store in the flashcard review storage
   */
  public static function rateCard($curData, $answer)
  {
    // promote or demote card
    if ($answer === uiFlashcardReview::UIFR_NO) {
      $card_box = 1; // failed pile
    }
    else if ($answer === uiFlashcardReview::UIFR_YES ||
             $answer === uiFlashcardReview::UIFR_EASY) {
      $card_box = $curData->leitnerbox + 1;
    } else {
      // uiFlashcardReview::UIFR_HARD
      $card_box = $curData->leitnerbox - 1;
      $card_box = max($card_box, 2);
    }

    // cards in the last box can not move higher, so they stay in the last box
    $card_box = min($card_box, self::MAXSTACKS);

    if ($answer === uiFlashcardReview::UIFR_HARD && $curData->leitnerbox == 2)
    {
      // cards in 1+ box with "hard" answer stay in 1+ box with 1 DAY interval
      $card_interval = 1;
    }
    else
    {
      // in all other cases, the interval is based on the new box + variance
      $card_interval = self::$SCHEDULE_DAYS[$card_box - 1];
      
      // easy answers get a higher interval
      if ($answer === uiFlashcardReview::UIFR_EASY) {
        $card_interval = (int)($card_interval * 1.5);
      }

      // add variance to spread due cards so that they don't all fall onto the same days
      $card_variance = self::$SCHEDULE_VARIANCE[$card_box - 1]; // days plus or minus
      $card_interval = ($card_interval - $card_variance) + rand(0, $card_variance * 2);
    }
    
    $user = sfContext::getInstance()->getUser(); // for sqlLocalTime()
    
    $sqlLocalTime      = UsersPeer::sqlLocalTime();
    $sqlExprExpireDate = sprintf('DATE_ADD(%s, INTERVAL %d DAY)', $sqlLocalTime, $card_interval);
    
    $oUpdate = array(
      'totalreviews'  => $curData->totalreviews + 1,
      'leitnerbox'    => $card_box,
      'lastreview'    => new coreDbExpr($sqlLocalTime),
      'expiredate'    => new coreDbExpr($sqlExprExpireDate)
    );
    
    if ($answer === uiFlashcardReview::UIFR_YES || $answer === uiFlashcardReview::UIFR_EASY) {
      $oUpdate['successcount'] = $curData->successcount + 1;
    }
    else {
      $oUpdate['failurecount'] = $curData->failurecount + 1;
    }
    
    return $oUpdate;
  }
  
  /**
   * Returns update data for flashcard to move back into the review cycle,
   * and out of the red stack. The card is rescheduled for review in 3 days.
   * 
   * @return Array   Updated data to store in the flashcard review storage
   */
  public static function relearnCard()
  {
    // move back into stack 2
    $card_box = 2;
    $card_interval = self::$SCHEDULE_DAYS[$card_box - 1];
    
    // fixme: don't store "localized" dates in database
    $sqlExprExpireDate = sprintf('DATE_ADD(%s, INTERVAL %d DAY)', UsersPeer::sqlLocalTime(), $card_interval);

    $oUpdate = array(
      'leitnerbox'    => 2,
      'expiredate'    => new coreDbExpr($sqlExprExpireDate)
    );
    
    return $oUpdate;
  }
  
}

