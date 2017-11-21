<?php
/**
 * LeitnerSRS handles the flashcard scheduling system.
 * 
 * 
 */

class LeitnerSRS
{
  const  FAILEDSTACK = 1;

  const  EASY_FACTOR = 1.5;

  static
    // Variance +/- for base interval for flashcard going in box N
    // Offset 0 = Leitner box 1 (box 1 = failed/untested, box 2 = 1() review, ...)
    $SCHEDULE_VARIANCE = array(0, 1, 2,  3,  5, 10,  15,  30);
 
  // return max Leitner Box, including Failed & New as box #1
  private static function getMaxBox()
  {
    static $cached = false;
    if (false === $cached) {
      $user   = sfContext::getInstance()->getUser();
      $cached = $user->getUserSetting('OPT_SRS_MAX_BOX') + 1;
      error_log('cache max box '.$cached);
    }
    else { error_log('cached '.$cached); }

    return $cached;
  }

  // return SRS multiplier setting as a float
  private static function getMultiplier()
  {
    $user = sfContext::getInstance()->getUser();
    $mult = (int) $user->getUserSetting('OPT_SRS_MULT');
    if ($mult < 100 || $mult > 500) {
      // in case something's wrong with the session? paranoia
      error_log('Invalid SRS multiplier: '.$mult.' (using default value)');
      $mult = 205;
    }
    $mult = $mult / 100;
error_log('getMultiplier() '.$mult);
    return $mult;
  } 

  // Return interval in days, for given box EXCLUDING the leftmost (so 1 means 1st interval)
  private static function getNthInterval(int $box)
  {
    static $intervals = null;

    assert('$box > 0');

    if (null === $intervals) {
      $max_box = self::getMaxBox() - 1; 
      $mult    = self::getMultiplier();
      $first   = 3.0;

      for ($n  = 0; $n < $max_box; $n++) {
        $days  = ceil($first * pow($mult, $n));
        $intervals[] = $days;
      }

      error_log('getNthInterval() cached: '.json_encode($intervals));
    }
    else { error_log(sprintf('getNthInterval(%d) = %d days', $box, $intervals[$box - 1])); }

    return $intervals[$box - 1];
  }

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

    // clamp highest box to SRS setting
    $card_box = min($card_box, self::getMaxBox());

    if ($answer === uiFlashcardReview::UIFR_HARD && $curData->leitnerbox == 2)
    {
      // cards in 1+ box with "hard" answer stay in 1+ box with 1 DAY interval
      $card_interval = 1;
    }
    else if ($card_box === 1)
    {
      // Failed pile
      $card_interval = 0;
    }
    else
    {
      // in all other cases, the interval is based on the new box + variance
      $card_interval = self::getNthInterval($card_box - 1);
      
      // easy answers get a higher interval
      if ($answer === uiFlashcardReview::UIFR_EASY) {
        $card_interval = (int)($card_interval * self::EASY_FACTOR);
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
}

