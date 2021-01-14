<?php
/**
 * rtkFlashcardSelection
 *
 * This class represents a selection of flashcards as an array of unique UCS-2
 * codes of the kanji/hanzi.
 *
 * The selection can be set in different ways:
 *
 *   setFromString()
 *   addHeisigRange()
 *
 * The array of selected flashcard can be obtained with getNumCards(). ids can then be read:
 *
 *   getCards()
 *   getNumCards()
 * 
 * 
 * @author     Fabrice Denis
 */

class rtkFlashcardSelection
{
  protected
    $cardIds  = [],
    $request  = null;
  
  /**
   * 
   * @param  object  $request  Object with setError() method
   * @return 
   */
  public function __construct($request)
  {
    $this->request = $request;
  }
  
  /**
   * Set array of flashcard ids, from a selection expressed as a string.
   * 
   * Accepts:
   *  Single cards      3
   *  Range of cards    4-25
   *  Kanji             <single utf8 char>
   *   
   * Delimiters:
   *  All flashcard ids (numerical or kanji) must be separated by commas,
   *  or spaces, or tabs. A range of cards can not have spaces around the dash.
   *  Kanji characters do not need to be separated between them but must be separated
   *  from the numerical indices eg:
   *  
   *   3, 42 15, 10-13 一年生
   * 
   * @param  string  $selString  Selection in string format
   * 
   * @return int   Number of cards in selection
   */
  public function setFromString($selString)
  {
    $this->cardIds = [];

    // split string on spaces, japanese space (0x3000) and comma
    $selparts = preg_split('/[,\s\x{3000}]+/u', $selString, -1, PREG_SPLIT_NO_EMPTY);
    if (!count($selparts))
    {
      return false;
    }
    
    foreach ($selparts as &$part)
    {
      // numerical range
      if (preg_match('/^([0-9]+)-([0-9]+)$/', $part, $matches))
      {
        $from = $matches[1];
        $to = $matches[2];
        if (!rtkIndex::isValidHeisigIndex($from) || !rtkIndex::isValidHeisigIndex($to))
        {
          $this->request->setError('if', sprintf('Invalid framenumber: "%s"', $part));
          return false;
        }
        elseif ($from > $to)
        {
          $this->request->setError('ir', sprintf('Invalid range: "%s"', $part));
          return false;
        }
        
        for ($i = $from; $i <= $to; $i++)
        {
          $this->cardIds[] = rtkIndex::getUCSForIndex($i);
        }
        
      }
      // numerical id
      elseif (ctype_digit($part))
      {
        $framenum = intval($part);
        if (!rtkIndex::isValidHeisigIndex($framenum))
        {
          $this->request->setError('if', sprintf('Invalid framenumber: "%s"', $part));
          return false;
        }
        $this->cardIds[] = rtkIndex::getUCSForIndex($framenum);
      }
      // utf8 character id
      elseif (CJK::hasKanji($part))
      {
        $cjkChars = CJK::getKanji($part);
        if (!count($cjkChars)) {
          continue;
        }

        foreach ($cjkChars as $cjk)
        {
          $ucsArr = utf8::toUnicode($cjk);
          if (is_array($ucsArr))
          {
            $this->cardIds[] = $ucsArr[0];
          }
          else
          {
            $this->request->setError('if', sprintf('Malformed character "%s" can not be parsed to UCS-2.', $cjk));
            return false;
          }
        }
      }
      else
      {
        $this->request->setError('ip', sprintf('Invalid part: "%s"', $part));
        return false;
      }
    }

    // remove duplicates
    $this->cardIds = array_unique($this->cardIds);

    return $this->getNumCards();
  }

  /**
   * Add cards in sequence order.
   * 
   * Selection should be a frame number to add up to,
   * or a number of cards to add "+10", filling in all missing cards in the RTK range.
   * 
   * @param string $selection  "56" (add up to 56), or "+20" (add 20 cards)
   * 
   * @return int   Number of cards in selection (also 0), or false if error
   */
  public function addHeisigRange($userId, $selection)
  {
    $this->cardIds = [];

    // get user'flashcards as indexes, do filter because cards not in the index will have extended frame nr (UCS code)
    $userCards = ReviewsPeer::getFlashcardsByIndex($userId, 'rtk1+3');
    
    // create a map of existing flashcards
    $inDeck = [];
    foreach ($userCards as $framenum)
    {
      $inDeck[$framenum] = true;
    }
    
    // add a number of cards, or up to frame number, fill in the missing cards
    if (preg_match('/^\+([0-9]+)$/', $selection, $matches))
    {
      $range = $matches[1];

      if ($range < 1)
      {
        $this->request->setError('x', 'Invalid range of cards');
        return false;
      }
     
      // "+N" cards: add up to N cards no in deck, in order
      for ($i = 1, $n = 0; $n < $range && $i <= rtkIndex::inst()->getNumCharacters(); $i++)
      {
        if (!isset($inDeck[$i]))
        {
          $this->cardIds[] = rtkIndex::getUCSForIndex($i);
          $n++;
        }
      }
    }
    else
    {
      $addTo = intval($selection);
      
      if (!rtkIndex::isValidHeisigIndex($addTo))
      {
        $this->request->setError('x', sprintf('Invalid index number: "%s"', $selection));
        return false;
      }

      for ($i = 1; $i <= $addTo; $i++)
      {
        if (!isset($inDeck[$i]))
        {
          $this->cardIds[] = rtkIndex::getUCSForIndex($i);
        }
      }
    }
    
    return $this->getNumCards();
  }


  /**
   * 
   * @return int  Number of items in selection
   */
  public function getNumCards()
  {
    return count($this->cardIds);
  }

  /**
   * 
   * @return array  Array of item ids (the selection)
   */
  public function getCards()
  {
    return $this->cardIds;
  }

  /**
   * Serialize methods to save user selection between requests.
   * 
   * @return 
   */
  public function __sleep()
  {
    return ['cardIds'];
  }

  public function __wakeup()
  {
  }
}
