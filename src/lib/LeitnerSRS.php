<?php

/**
 * Helper function to return the user's SRS settings (only the OPT_SRS_* keys).
 *
 * @return array
 */
function koohiiGetUserSettingsSRS()
{
  $user = sfContext::getInstance()->getUser();

  $opts = [
    'OPT_SRS_MULT' => $user->getUserSetting('OPT_SRS_MULT'),
    'OPT_SRS_HARD_BOX' => $user->getUserSetting('OPT_SRS_HARD_BOX'),
    'OPT_SRS_MAX_BOX' => $user->getUserSetting('OPT_SRS_MAX_BOX'),
  ];

  assert(is_int($opts['OPT_SRS_MULT']));
  assert(is_int($opts['OPT_SRS_HARD_BOX']));
  assert(is_int($opts['OPT_SRS_MAX_BOX']));
  assert($opts['OPT_SRS_MULT'] >= 100 && $opts['OPT_SRS_MULT'] <= 500);

  return $opts;
}

/**
 * This class handles the SRS scheduling, based on card rating (no/yes/easy/etc).
 *
 * getInstance() should always be used, which injects user's SRS settings automatically.
 */
class LeitnerSRS
{
  public const FAILEDSTACK = 1;

  public const EASY_FACTOR = 1.5;

  public const VARIANCE_FACTOR = 0.15;
  public const VARIANCE_LIMIT = 30;   // days

  public const DEFAULT_SRS_MULT = 2.05;
  public const DEFAULT_SRS_MAX_BOX = 7;
  public const DEFAULT_SRS_HARD_BOX = 0;

  // max Leitner Box *including* Failed & New as box #1
  private int $optMaxBox;

  // upper limit for Hard answer (excludes Failed&New box), 1 = 1+ reviews)
  private int $optHardBox;

  // multiplier to calculate incremental review intervals
  private float $optMult;

  /** @var int[] intervals in *days* for each review pile (indexed from 0 = 1+ reviews) */
  protected array $intervals;

  /** @var int[] */
  protected array $variance;

  /**
   * Does NOT handle defaults -- defaults come from UsersSettingsPeer (for now).
   *
   * @param array $options ... array containing the OPT_SRS_* settings
   */
  public function __construct($options)
  {
    $this->config($options);
    // DBG::printr($this);
  }

  /**
   * Useful method for running tests.
   *
   * @param array $options
   */
  protected function config($options)
  {
    // convert to float (stored user setting 205 means 2.05)
    $this->optMult = $options['OPT_SRS_MULT'] / 100;

    // 0 means default (within the range of OPT_SRS_MAX_BOX)
    $this->optHardBox = $options['OPT_SRS_HARD_BOX'] ?: $options['OPT_SRS_MAX_BOX'] - 1;

    // adjust for the internal logic (1  = Failed&New box)
    $this->optMaxBox = $options['OPT_SRS_MAX_BOX'];

    $this->intervals = $this->calcIntervals();

    $this->variance = $this->calcVariance();
  }

  /**
   * Convenience method which automatically injects the user's SRS settings.
   */
  public static function getInstance(): self
  {
    static $instance = null;
    $instance ??= new LeitnerSRS(koohiiGetUserSettingsSRS());

    return $instance;
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
   * Returns an array with only the values that have changed, plus:
   *
   *   interval   ... in days, to update the card's `expiredate`
   *
   * @param object $curData Row data coming from flashcard review storage
   * @param string $answer  Answer (see uiFlashcardReview.php const)
   *
   * @return array Row data to store in the flashcard review storage
   */
  public function rateCard(object $curData, string $answer)
  {
    $card_interval = 0;
    $card_variance = 0;

    switch ($answer) {
      case uiFlashcardReview::RATE_NO:
        $card_box = 1; // failed pile

        break;

      case uiFlashcardReview::RATE_YES:
      case uiFlashcardReview::RATE_EASY:
        $card_box = $curData->leitnerbox + 1;

        break;

      case uiFlashcardReview::RATE_HARD:
        $card_box = $curData->leitnerbox - 1;

        // clamp bottom
        $card_box = max(2, $card_box);

        // clamp top
        $card_box = min($card_box, $this->optHardBox + 1);

        break;
    }

    // clamp highest box to SRS setting
    $card_box = min($card_box, $this->optMaxBox + 1);

    if ($card_box === 2 && $answer === uiFlashcardReview::RATE_HARD)
    {
      // cards in NEW or 1+ REVIEW piles with HARD answer get a fixed 1 day interval
      $card_interval = 1;
      $card_variance = 0;
    // error_log(sprintf('RATING [ Hard ] box %d > %d, scheduled in 1 day', $curData->leitnerbox, $card_box));
    }
    elseif ($card_box === 1)
    {
      // failed pile
      $card_interval = 0;
      $card_variance = 0;
    // error_log(sprintf('RATING [ Fail ] box %d > 1', $curData->leitnerbox));
    }
    else
    {
      // in all other cases, the interval is based on the new box + variance
      $card_interval = $this->intervals[$card_box - 1];

      // easy answers get a higher interval
      if ($answer === uiFlashcardReview::RATE_EASY)
      {
        $card_interval = ceil($card_interval * self::EASY_FACTOR);
      }

      // add variance to spread due cards so that they don't all fall onto the same days
      $card_variance = $this->variance[$card_box - 1];
      $card_interval = ($card_interval - $card_variance) + rand(0, $card_variance * 2);

      // error_log(sprintf('RATING [ %s ] box %d => %d, scheduled in %d days (f %d)', $answer, $curData->leitnerbox, $card_box, $card_interval, $card_variance));
    }

    $user = sfContext::getInstance()->getUser(); // for sqlLocalTime()
    $sqlLocalTime = UsersPeer::sqlLocalTime();
    $sqlExprExpireDate = sprintf('DATE_ADD(%s, INTERVAL %d DAY)', $sqlLocalTime, $card_interval);

    $oUpdate = [
      'totalreviews' => $curData->totalreviews + 1,
      'leitnerbox' => $card_box,
      'lastreview' => new coreDbExpr($sqlLocalTime),
      'expiredate' => new coreDbExpr($sqlExprExpireDate),
    ];
    // echo "*** expiredate *** {$card_interval} \n";

    if ($answer === uiFlashcardReview::RATE_YES || $answer === uiFlashcardReview::RATE_EASY)
    {
      $oUpdate['successcount'] = $curData->successcount + 1;
    }
    else
    {
      $oUpdate['failurecount'] = $curData->failurecount + 1;
    }

    return $oUpdate;
  }

  /**
   * Return an array of base intervals (prior to adding some variance),
   * for each review pile.
   *
   * Starts at index 1 for 1+ review pile.
   *
   * @return int[]
   */
  private function calcIntervals()
  {
    $intervals = [0];

    $BASE_INTERVAL = 3.0; // 3 days for the first pile

    for ($reviewPile = 1; $reviewPile <= $this->optMaxBox; ++$reviewPile)
    {
      $intervals[] = (int) ceil($BASE_INTERVAL * pow($this->optMult, $reviewPile - 1));
    }

    return $intervals;
  }

  /**
   * Return an array of "variance" -- small intervals used to spread card's due
   * date so they don't all expire on the same due date.
   *
   * Starts at index 1 for 1+ review pile.
   *
   * @return int[]
   */
  private function calcVariance()
  {
    assert(isset($this->intervals));
    $variance = [0];

    for ($i = 1; $i <= $this->optMaxBox; ++$i)
    {
      $variance[] = (int) min(self::VARIANCE_LIMIT, ceil(self::VARIANCE_FACTOR * $this->intervals[$i]));
    }

    return $variance;
  }
}
