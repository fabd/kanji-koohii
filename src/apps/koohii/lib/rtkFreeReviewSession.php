<?php
/**
 * rtkFreeReviewSession maintains the state of flashcard answers during a non
 * SRS review session. The answers can then be passed to the Review Summary.
 *
 * Usage:
 *
 *   Instancing the class to create, or re-create (from session):
 *     $oFRS = new rtkFreeReviewSession(true);
 *
 *   Restore from the session:
 *     $oFRS = new rtkFreeReviewSession();
 * 
 * Methods:
 *
 *   getOptions()
 *   createFlashcardSet($from, $to)
 *   updateFlashcard($ucsId, $answer)
 *   getReviewedCards()
 */

class rtkFreeReviewSession
{
  protected
    $user         = null,
    $cardstatus   = null,
    $options      = null;

  /**
   * Session variable which stores flashcard data and answers.
   */
  const SESS_CARD_ANSWERS = 'freemode_session';

  /**
   * Instance the flashcard review session, to start it or get access to the
   * stored data.
   *
   * @param  bool    $start     True to intialize the session
   *
   * @constructor
   */
  public function __construct($start = false)
  {
    $this->user = sfContext::getInstance()->getUser();
    
    if ($start)
    {
      $this->cardstatus = array();

      $this->user->setAttribute(self::SESS_CARD_ANSWERS, array());
    }
    else
    {
      // restore flashcard update status from the session
      $this->cardstatus = $this->user->getAttribute(self::SESS_CARD_ANSWERS, array());
    }
  }

  /**
   * Create an array of flashcard ids using sequence numbers.
   *
   * @param   int     $from   Sequence number start
   * @param   int     $to     Sequence number end
   * @param   bool    $shuffle      True to randomize the cards
   *
   * @return array
   */
  public function createFlashcardSet($from, $to, $shuffle = false)
  {
    // create array of UCS ids from sequential Heisig flashcard range
    $numCards = $to - $from + 1;
    $framenums = array_fill(0, $numCards, 1);
    for ($i = 0; $i < $numCards; $i++) {
      $framenums[$i] = $from + $i;
    }

    if ($shuffle) {
      // shuffle
      shuffle($framenums);
    }

    $ids = rtkIndex::convertToUCS($framenums);

    return $ids;
  }

  /**
   * Sets flashcard answer in session.
   *
   * @param   int     $ucsId    UCS-2 code point
   * @param   int     $answer
   *
   */
  public function updateFlashcard($ucsId, $answer)
  {
    $this->cardstatus[$ucsId] = $answer;

    $this->user->setAttribute(self::SESS_CARD_ANSWERS, $this->cardstatus);
  }

  /**
   * Returns the flashcard selection or flashcard data.
   *
   * @Return array
   */
  public function getReviewedCards()
  {
    return $this->cardstatus;
  }
}
