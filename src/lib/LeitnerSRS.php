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
 * Always use getInstance() to inject the user's SRS settings automatically.
 *
 *   LeitnerSRS::getInstance()->rateCard()
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

  // flashcard ratings (@see FlashcardReview.ts, flashcards.d.ts)
  public const RATE_NO = 'no';
  public const RATE_HARD = 'hard';
  public const RATE_YES = 'yes';
  public const RATE_EASY = 'easy';
  public const RATE_DELETE = 'delete';
  public const RATE_SKIP = 'skip';
  public const RATE_AGAIN = 'again';
  public const RATE_AGAIN_HARD = 'again-hard';
  public const RATE_AGAIN_YES = 'again-yes';
  public const RATE_AGAIN_EASY = 'again-easy';

  // max Leitner Box (excludes Fail & New box, 1 = 1+ reviews)
  private int $optMaxBox;

  // upper limit for Hard answer (excludes Fail & New box, 1 = 1+ reviews)
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
    // LOG::info(__CLASS__, $this);
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
   * @param array  $curData Row data coming from flashcard review storage
   * @param string $answer  Answer (see FlashcardReview.php const)
   *
   * @return array|false Row data to store in the flashcard review storage
   */
  public function rateCard(array $curData, string $answer)
  {
    $card_box = $curData['leitnerbox'];
    $card_interval = 0;
    $card_variance = 0;

    if (!$this->isValidRating($answer))
    {
      return false;
    }

    // handle again-* ratings (again followed by hard/yes/easy during review)
    if ($answer === LeitnerSRS::RATE_AGAIN_HARD)
    {
      $answer = LeitnerSRS::RATE_HARD;
      $card_box = 1;
    }
    if ($answer === LeitnerSRS::RATE_AGAIN_YES)
    {
      $answer = LeitnerSRS::RATE_YES;
      $card_box = 1;
    }
    if ($answer === LeitnerSRS::RATE_AGAIN_EASY)
    {
      $answer = LeitnerSRS::RATE_EASY;
      $card_box = 1;
    }

    switch ($answer) {
      case LeitnerSRS::RATE_NO:
        $card_box = 1;

        break;

      case LeitnerSRS::RATE_AGAIN:
        // "again" cards pre-emptively go to the fail pile
        $card_box = 1;

        break;

      case LeitnerSRS::RATE_YES:
      case LeitnerSRS::RATE_EASY:
        $card_box = $card_box + 1;

        break;

      case LeitnerSRS::RATE_HARD:
        $card_box = $card_box - 1;

        // HARD answers can not fall back into the failed pile
        $card_box = max(2, $card_box);

        // apply “Max Box for Hard Answer" setting
        $card_box = min($card_box, $this->optHardBox + 1);

        break;
    }

    // clamp highest box to SRS setting
    $card_box = min($card_box, $this->optMaxBox + 1);

    if ($card_box === 2 && $answer === LeitnerSRS::RATE_HARD)
    {
      // cards in NEW or 1+ REVIEW piles with HARD answer get a fixed 1 day interval
      $card_interval = 1;
      $card_variance = 0;
    }
    elseif ($card_box === 1)
    {
      // failed pile
      $card_interval = 0;
      $card_variance = 0;
    }
    else
    {
      // in all other cases, the interval is based on the new box + variance
      $card_interval = $this->intervals[$card_box - 1];

      // easy answers get a higher interval
      if ($answer === LeitnerSRS::RATE_EASY)
      {
        $card_interval = (int) ceil($card_interval * self::EASY_FACTOR);
      }

      // add variance to spread due cards so that they don't all fall onto the same days
      $card_variance = $this->variance[$card_box - 1];
      $card_interval = ($card_interval - $card_variance) + rand(0, $card_variance * 2);
    }

    $oUpdate = [
      'leitnerbox' => $card_box,
      'interval_days' => $card_interval,
    ];

    // FIXME for now it can be that a card can get multiple AGAIN ratings during
    //       a session while syncing to server, so just ignore AGAIN although the
    //       card moves to the failed pile.
    //
    //       This is to avoid multiple failure/totalreview increases as an AGAIN
    //       card could realistically be synced 2+ times in a review.
    //
    if ($answer !== LeitnerSRS::RATE_AGAIN)
    {
      $oUpdate['totalreviews'] = $curData['totalreviews'] + 1;

      if ($this->isSuccessCount($answer))
      {
        $oUpdate['successcount'] = $curData['successcount'] + 1;
      }
      else
      {
        $oUpdate['failurecount'] = $curData['failurecount'] + 1;
      }
    }

    return $oUpdate;
  }

  /**
   * Returns true if the rating is considered a "success" review
   * (for now this only for the displayed stats in the Review Summary / Flashcard List).
   *
   *   HARD is considered difficult recall, but still recalled.
   *
   *   AGAIN_(HARD|YES|EASY) is a lapsed review (forgotten).
   */
  private function isSuccessCount(string $answer): bool
  {
    return in_array($answer, [
      LeitnerSRS::RATE_HARD,
      LeitnerSRS::RATE_YES,
      LeitnerSRS::RATE_EASY,
    ]);
  }

  /**
   * Check that the rating is known and valid.
   *
   * Note: we don't check for eg. SKIP, DELETE which are not handled here.
   */
  public function isValidRating(string $rating): bool
  {
    return in_array($rating, [
      LeitnerSRS::RATE_NO,
      LeitnerSRS::RATE_HARD,
      LeitnerSRS::RATE_YES,
      LeitnerSRS::RATE_EASY,
      LeitnerSRS::RATE_AGAIN,
      LeitnerSRS::RATE_AGAIN_HARD,
      LeitnerSRS::RATE_AGAIN_YES,
      LeitnerSRS::RATE_AGAIN_EASY,
    ], /* strict check*/ true);
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
