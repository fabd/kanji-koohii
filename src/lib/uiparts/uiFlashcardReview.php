<?php
/**
 * uiFlashcardReview
 *
 * This is a generic class that handles JSON requests to return flashcard data to the client,
 * and to update the database with data coming from the client.
 *
 * It keeps an array in the session to check that the same card is not updated
 * twice, which can happen if a message is lost and the client resends the
 * flashcard update request.
 *
 * The type of data on the flashcards, the handling of review status and review updates are
 * all managed by callback functions.
 * 
 * The only fixed information is that each flashcard must have a unique ID and this id
 * is the one that identifies flashcards between the server and client. The id must be
 * numeric!
 * 
 * 
 * Methods:
 * 
 *   handleRequest()
 * 
 * 
 * 
 * Options (constructor):
 * 
 *  WHEN HANDLING AJAX REQUESTS FOR CARDS (Post requests)
 *
 *   fn_get_flashcard   callable
 *   fn_put_flashcard   callable
 * 
 *   fn_get_flashcard(int $id, object $options)   
 *                      Returns flashcard data as associative array or object.
 *                      Returns null if the data can not be retrieved (id invalid, ...).
 *                      Returned "id" property must be integer! (cf. uiFlashcardReview.js)
 * 
 *   fn_put_flashcard(int $id, object $data)
 *                      Update the flashcard status, and anything else based on data
 *                      received from client.
 *                      $id is the same as $data->id, and must be sanitized.
 *                      Returns false if the update was not succesfull.
 *  
 *
 * Format of data passed to handleRequest():
 * 
 *   get                  An array of item ids
 *                        [1, 2, 3, ...]
 *
 *   opt                  Options sent with the request, passed to fn_get_flashcard()
 *
 *   put                  An array of flashcard update data as objects, each object
 *                        has "id" property.
 *                        [ {id: 1, ... }, { id:2, ... }, ... ]
 *
 * 
 * Format of response:
 * 
 *   get                  An array of flashcard data as objects, each object has an "id"
 *                        property that matches one of the ids from the JSON request.
 * 
 *   put                  If there was a "put" request, returns the ids of items that
 *                        were succesfully handled. On the front end side, any items
 *                        that were not succesfully handled may be sent again.
 *
 *
 */

class uiFlashcardReview
{
  private sfUser $user;
  private object $options;
  private array $cardStatus;
  
  // card ratings (@see FlashcardReview.js, flashcards.d.ts)
  const RATE_NO      = 'no';
  const RATE_HARD    = 'hard';
  const RATE_YES     = 'yes';
  const RATE_EASY    = 'easy';
  const RATE_DELETE  = 'delete';
  const RATE_SKIP    = 'skip';
  const RATE_AGAIN   = 'again';
  
  const RATE_AGAIN_HARD = 'again-hard';
  const RATE_AGAIN_YES  = 'again-yes';
  const RATE_AGAIN_EASY = 'again-easy';

  /**
   * Do not allow client to prefetch too many cards at once.
   */
  const MAX_PREFETCH = 20;

  /**
   * Do not allow client to sent too many card updates at once.
   */
  const MAX_UPDATE   = 20;

  /**
   * Name of session variable which stores a boolean status for each
   * flashcard. This array is checked to avoid updating a card twice,
   * for those times when the client doesn't get a response, and sends
   * flashcard answers more than once.
   */
  const SESS_ATTR_NAME = 'uifr_card_answers';

  /**
   * 
   * @param array   $options   See documentation
   * @param boolean $start     Must be true when instancing at beginning of review session
   * 
   * @return 
   */  
  public function __construct(array $options = [], $start = false)
  {
    $this->options = (object) $options;

    $this->user = sfContext::getInstance()->getUser();

    // start a new review session, or restore state from session
    if ($start)
    {
      $this->cardStatus = [];
      $this->saveToSession();
    }
    else
    {
      $this->cardStatus = $this->user->getAttribute(self::SESS_ATTR_NAME, []);
    }
  }

  /**
   * Handles request from the client side FlashcardReview
   * 
   * @param object $fcrData   Request from FlashcardReview client side
   * 
   * @return object   Response to be encoded as a JSON response
   */
  public function handleRequest(object $fcrData)
  {
    $oResponse = new stdClass;

    // get flashcard data
    if (isset($fcrData->get) && is_array($fcrData->get))
    {
      $get_cards = [];

      // flashcard options
      $cardOpts = $fcrData->opt ?? new stdClass;

      // do not accept too large prefetch (tampering with ajax request on client)
      if (count($fcrData->get) > self::MAX_PREFETCH) {
        $fcrData->get = array_slice($fcrData->get, 0, self::MAX_PREFETCH);
      }
      
      foreach ($fcrData->get as $id)
      {
        assert(is_int($id));

        $cardData = call_user_func($this->options->fn_get_flashcard, $id, $cardOpts);
        
        if ($cardData === null) {
          throw new rtkAjaxException('Could not fetch item "'.$id.'" in JSON request');
        }
        $get_cards[] = $cardData;
      }
      
      if (count($get_cards)) {
        $oResponse->get = $get_cards;
      }
    }

    // update flashcard reviews
    if (isset($fcrData->put) && is_array($fcrData->put))
    {
      // don't update more than MAX_UPDATE cards, client will know
      // because the success status is returned only for updated cards
      $items = array_slice($fcrData->put, 0, self::MAX_UPDATE);

      $oResponse->put = $this->handlePutRequest($items);
    }

// simulate timeout
// if (isset($fcrData->flush)) { sleep(2.5); }

    return $oResponse;
  }

  /**
   * Update flashcards based on an array of update data, maintains the status
   * array that flags card that have already been updated this session.
   *
   * @param   array   items   An array of flashcard answers from client in the form
   *                        { id: number, ... } (cf. TCardAnswer)
   * 
   * @return  array    An array containing the id of succesfully updated items.
   */
  public function handlePutRequest(array $items)
  {
    if (!isset($this->options->fn_put_flashcard)) {
      throw new rtkAjaxException('uiFlashcardReview: fn_put_flashcard is not set');
    }

    $putSuccess = [];
    
    foreach ($items as $answer)
    {
      if (!is_object($answer)) {
        continue;
      }

      $cardId = (int)$answer->id;
      $cardStatus = $this->getCardStatus($cardId);
      

      // if a card is rated AGAIN, it may be rated again in the same session.
      // 
      // Otherwise, avoid duplicate ratings in case the server somehow timed out
      //  but did process, and the client resends the answers.
      //
      if ($cardStatus && $cardStatus !== uiFlashcardReview::RATE_AGAIN)
      {
        $putSuccess[] = $cardId;
      }
      // Currently the client does not expect for errors to happen.
      // If an error happens, assume it is a temporary hiccup and ignore the
      // flashcard, so that the client will send another put request.
      else if (true === call_user_func($this->options->fn_put_flashcard, $cardId, $answer))
      {
        $putSuccess[] = $cardId;

        // Flag the card as updated, in case the server response times out for the
        //  client, and the client re-sends the same card answers, avoid rating a card twice.
        //
        $this->updateCard($cardId, $answer->r);
      }
    }


    return $putSuccess;
  }

  /**
   * Map old ratings (pre-Nov 2021) to new ones (for compatiblity with Kanji Ryokucha).
   * 
   * @return array
   */
  public static function normalizeOldRatings(array $items) {
    //
    $oldRatings = [
      1 => self::RATE_NO,
      2 => self::RATE_YES,
      3 => self::RATE_EASY,
      4 => self::RATE_DELETE,
      5 => self::RATE_SKIP,
      'h' => self::RATE_HARD
    ];

    foreach($items as &$item)
    {
      $item->r = $oldRatings[$item->r] ?? $item->r;
    }

    return $items;
  }

  private function saveToSession()
  {
    $this->user->setAttribute(self::SESS_ATTR_NAME, $this->cardStatus);
  }

  /**
   * Returns answer for this flashcard in this review session.
   * 
   * @param int   $id   Flashcard id.
   * 
   * @return string|false
   */
  private function getCardStatus(int $id)
  {
    return $this->cardStatus[$id] ?? false;
  }

  /**
   * Store flashcard answers.
   *
   * @param int $id   Flashcard id (UCS code for kanjis).
   */
  public function updateCard(int $id, string $rating)
  {
    $this->cardStatus[$id] = $rating;
    $this->saveToSession();
  }

  public function getCachedAnswers()
  {
    return $this->cardStatus;
  }
}
