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
 * Options (constructor):
 * 
 *  WHEN HANDLING AJAX REQUESTS FOR CARDS (Post requests)
 *
 *   fn_get_flashcard   callable
 *   fn_put_flashcard   callable
 *
 * Callback signatures:
 * 
 *   fn_get_flashcard(int $id, object $options)   
 *                      Returns flashcard data as associative array or object.
 *                      Returns null if the data can not be retrieved (id invalid, ...).
 *                      Returned "id" property must be integer! (cf. uiFlashcardReview.js)
 *   fn_put_flashcard(int $id, object $data)
 *                      Update the flashcard status, and anything else based on data received from client.
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
 *   put                  An array of flashcard update data as objects, each object has "id" property.
 *                        [ {id: 1, ... }, { id:2, ... }, ... ]
 *
 * 
 * Format of response:
 * 
 *   get                  An array of flashcard data as objects, each object has an "id" property that
 *                        matches one of the ids from the JSON request.
 *   put                  If there was a "put" request, returns the ids of items that were succesfully
 *                        handled. On the front end side, any items that were not succesfully handled
 *                        may be sent again.
 *
 * Usage:
 *    
 *   Create an instance in the action:
 *   =>  $this->uiFR = new uiFlashcardReview('module/partial', array(...))
 *
 */

class uiFlashcardReview
{
  protected sfUser $user;
  protected object $options;
  protected array $updated;
  
  // card ratings (@see FlashcardReview.js, flashcards.d.ts)
  const RATE_NO      = 'no';
  const RATE_HARD    = 'hard';
  const RATE_YES     = 'yes';
  const RATE_EASY    = 'easy';
  const RATE_DELETE  = 'delete';
  const RATE_SKIP    = 'skip';
  const RATE_AGAIN   = 'again';

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
  const SESS_CARD_UPDATED = 'uifr_cards_status';

  /**
   * 
   * @param array   $options   See documentation
   * @param boolean $start     Must be true when instancing at beginning of review session
   * 
   * @return 
   */  
  public function __construct(array $options, $start = false)
  {
    $this->options = (object) $options;

    $this->user = sfContext::getInstance()->getUser();

    // start a new review session, do some cleanup
    if ($start)
    {
      $this->updated = [];
      $this->cacheUpdateStatus();
    }
    else
    {
      // restore flashcard update status from the session
      $this->updated = $this->user->getAttribute(self::SESS_CARD_UPDATED, []);
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
   * @param   array   items   An array of flashcard update objects, each object
   *                          MUST have an "id" uniquely identifying this item.
   *                          Eg. (json) [ {id: 1, ... }, { id:2, ... }, ... ]
   * 
   * @return  array    An array containing the id of succesfully updated items.
   */
  public function handlePutRequest(array $items)
  {
    if (!isset($this->options->fn_put_flashcard)) {
      throw new rtkAjaxException('uiFlashcardReview: fn_put_flashcard is not set');
    }

    $putSuccess = [];
    
    foreach ($items as $oPutData)
    {
      if (!is_object($oPutData)) {
        continue;
      }

      $cardId = (int)$oPutData->id;

      // If the card is already updated, that means the client did not receive
      // the last response. Don't update the same card twice, but do return the
      // success status so that the client will clear the postCache.
      if (true === $this->getUpdateStatus($cardId))
      {
        $putSuccess[] = $cardId;
      }
      // Currently the client does not expect for errors to happen.
      // If an error happens, assume it is a temporary hiccup and ignore the
      // flashcard, so that the client will send another put request.
      else if (true === call_user_func($this->options->fn_put_flashcard, $cardId, $oPutData))
      {
        $putSuccess[] = $cardId;

        // Flag the card as updated. Sometimes the client does not receive the response.
        // When that happens the client will send duplicate "put" requests, we set a 
        // flag to avoid updating a card twice.
        $this->setUpdateStatus($cardId);
      }
    }

    $this->cacheUpdateStatus();

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

  public function cacheUpdateStatus()
  {
    $this->user->setAttribute(self::SESS_CARD_UPDATED, $this->updated);
  }

  /**
   * Returns true if the flashcard answer has already been handled.
   *
   * @param int   $id   Flashcard id.
   */
  protected function getUpdateStatus($id)
  {
    return isset($this->updated[$id]);
  }

  /**
   * Sets flag to makr that flashcard answer has been handled.
   *
   * @param int   $id   Flashcard id.
   */
  protected function setUpdateStatus($id)
  {
    $this->updated[$id] = true;
  }
}
