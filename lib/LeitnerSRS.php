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

  const  VARIANCE_FACTOR = 0.15;
  const  VARIANCE_LIMIT  = 30;   // days

  // returns upper limit for Hard answer (excluding failed&new pile, 1 = 1+ reviews) 
  private static function getHardIntervalLimit()
  {
    static $cached = false;
    if (false === $cached) {
      $user   = sfContext::getInstance()->getUser();
      $cached = $user->getUserSetting('OPT_SRS_HARD_BOX');

      // 0 means use default behaviour
      $cached = $cached > 0 ? $cached : self::getMaxBox() - 1;
    }
    return $cached;
  }

  // return max Leitner Box, including Failed & New as box #1
  private static function getMaxBox()
  {
    static $cached = false;
    if (false === $cached) {
      $user   = sfContext::getInstance()->getUser();
      $cached = $user->getUserSetting('OPT_SRS_MAX_BOX') + 1;
    }
    return $cached;
  }

  // return SRS multiplier setting as a float
  private static function getMultiplier()
  {
    $user = sfContext::getInstance()->getUser();
    $mult = (int) $user->getUserSetting('OPT_SRS_MULT');
    if ($mult < 100 || $mult > 500) {
      error_log(sprintf('Invalid SRS multiplier: %d (using default value) (uid %d)', $mult, $user->getUserId()));
      // in case something's wrong with the session? paranoia
      $mult = 205;
    }
    $mult = $mult / 100;

    return $mult;
  } 

  // return interval in days for nth review box (excluding failed&new pile, 1 = 1+ reviews)
  private static function getNthInterval(int $box)
  {
    static $intervals = null;

    assert('$box > 0');

    if (null === $intervals) {
      $max_box = self::getMaxBox() - 1; 
      $mult    = self::getMultiplier();
      $first   = 3.0;

      for ($n  = 0; $n < $max_box; $n++) {
        $intervals[] = ceil($first * pow($mult, $n));
      }
      // error_log('getNthInterval() CACHE => '.json_encode($intervals));
    }

    return $intervals[$box - 1];
  }

  // return variance in days for nth review box (excluding failed&new pile, 1 = 1+ reviews)
  private static function getNthVariance(int $box)
  {
    static $variance = null;
    if (null === $variance) {
      $max_box = self::getMaxBox() - 1; 
      for ($n = 1; $n <= $max_box; $n++) {
        $variance[] = min(self::VARIANCE_LIMIT, ceil(self::VARIANCE_FACTOR * self::getNthInterval($n)));
      }
      // error_log('getNthVariance() CACHE => '.json_encode($variance));
    }

    return $variance[$box - 1];
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
    }
    else if ($answer === uiFlashcardReview::UIFR_HARD) {

      $card_box = $curData->leitnerbox - 1;

      // clamp bottom
      $card_box = max(2, $card_box);

      // clamp top
      $card_box = min($card_box, (self::getHardIntervalLimit() + 1));
    }

    // clamp highest box to SRS setting
    $card_box = min($card_box, self::getMaxBox());

    if ($answer === uiFlashcardReview::UIFR_HARD && $curData->leitnerbox <= 2)
    {
      // cards in "1+" box OR the "New" pile with "hard" answer get a fixed 1 day interval
      $card_interval = 1;
      $card_variance = 0;

      // error_log(sprintf('RATING [ Hard ] box 2 > 2, scheduled in 1 day'));
    }
    else if ($card_box === 1)
    {
      // Failed pile
      $card_interval = 0;
      $card_variance = 0;

      // error_log(sprintf('RATING [ Fail ] box %d > 1', $curData->leitnerbox));
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
      $card_variance = self::getNthVariance($card_box - 1);
      $card_interval = ($card_interval - $card_variance) + rand(0, $card_variance * 2);

      // $s_rating = [1 => 'No', 'h' => 'Hard', 2 => 'Yes', 3 => 'Easy', 4 => 'Delete', 5 => 'Skip'];
      // error_log(sprintf('RATING [ %s ] box %d => %d, scheduled in %d days (f %d)',
      //   $s_rating[$answer], $curData->leitnerbox, $card_box, $card_interval, $card_variance));
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

