<?php
/**
 * View/Edit Story component.
 * 
 * This component main action is designed to work as part of the Study page,
 * but also as a ajax component on the Flashcard Review pages.
 * 
 * @author  Fabrice Denis
 */

class EditStoryComponent extends sfComponent
{
  // limit for storage (in characters)
  const MAXIMUM_STORY_LENGTH = 512;

  /**
   * Return EditStory component based on GET or POST request.
   * 
   * PARAMS
   *   kanjiData      Kanji data for kanji id
   *   reviewMode     True if called from the Review page
   *   custKeyword    (Study page action)
   *   
   * POST  requests to update the story for current user.
   * 
   *   doUpdate       Submit button text
   *   ucs_code       UCS-2 code value of the kanji
   *   chkPublic      Public flag: 1 or 0
   *   txtStory       Story textarea
   * 
   * @param coreWebRequest $request
   */
  public function execute($request)
  {
    $userId = $this->getUser()->getUserId();
    $ucsId  = $this->kanjiData->ucs_id;

    $savedStory = StoriesPeer::getStory($userId, $ucsId);
    $savedStoryText = $savedStory ? $savedStory->text : '';

    if ($request->getMethod() !== sfRequest::POST)
    {
      if ($savedStory)
      {
        $request->getParameterHolder()->add(array(
          'txtStory'  => $savedStory->text,
          'chkPublic' => $savedStory->public
        ));
      }
    }
    else
    {

      if ($request->hasParameter('doUpdate'))
      {
        $txtStory = trim($request->getParameter('txtStory', ''));
        $txtStory = strip_tags($txtStory);

        // validate story length with helpful message
        mb_internal_encoding('utf-8');
        $count = mb_strlen($txtStory);
        if ($count > self::MAXIMUM_STORY_LENGTH) {
          $n = $count - self::MAXIMUM_STORY_LENGTH;
          $request->setError('length', sprintf('Story is too long (512 characters maximum, %d over the limit).', $n));
        }
        
        if (true !== ($sError = $this->validateStory($txtStory)))
        {
          $request->setError('x', $sError);
        }

        if (!$request->hasErrors())
        {
          if (empty($txtStory))
          {
            // delete empty story
            StoriesPeer::deleteStory($userId, $ucsId);
            $savedStoryText = '';
          }
          else
          {
            $txtStory = $this->substituteKanjiLinks($txtStory);

            if (StoriesPeer::updateStory($userId, $ucsId, array(
                'text'     => $txtStory,
                'public'   => $request->hasParameter('chkPublic') ? 1 : 0)))
            {
              $savedStoryText = $txtStory;
            }
          }

          // FIXME for now always invalidate the cache
          StoriesSharedPeer::invalidateStoriesCache($ucsId);
        }

        $request->setParameter('txtStory', $txtStory);
      }
    }

    // Learned button for Study page only
    if (!$request->hasParameter('reviewMode'))
    {
      $this->isRestudyKanji = ReviewsPeer::isFailedCard($userId, $ucsId);
      $this->isRelearnedKanji = LearnedKanjiPeer::hasKanji($userId, $ucsId);
    }


    // ONLY for flashcard reviews -- get favorite story, if user's story is empty
    $this->isFavoriteStory = false;
    if ($this->reviewMode && $savedStoryText === '')
    {
      if (false !== ($favStory = StoriesPeer::getFavouriteStory($userId, $ucsId)))
      {
        $savedStoryText = $favStory->text; // story to format
        $this->isFavoriteStory = true;
      }
    }

    // set the view/edit story text (note client-side has view, edit, and cancel changes UI)
    $keyword = ($this->custKeyword !== null) ? $this->custKeyword : $this->kanjiData->keyword;
    $this->formatted_story = StoriesPeer::getFormattedStory($savedStoryText, $keyword, true);

    return sfView::SUCCESS;
  }

  /**
   * Checks that all the kanji links (using {...} notation) are valid and point
   * to an existing character.
   *
   * @return  bool    True if all links in the story are valid.
   */
  private function validateStory($text)
  {
    $result = preg_match_all('/{([^}]*)}/u', $text, $matches);

    foreach ($matches[1] as $match)
    {
      $valid = true;

      if (ctype_digit($match)) {
        $valid = rtkIndex::isValidHeisigIndex((int)$match) || CJK::isCJKUnifiedUCS((int)$match);
      } else {
        $valid = CJK::isKanjiChar($match);
      }
      
      if (!$valid) {
        return sprintf('The link {%s} is not a valid Heisig frame number, or CJK Unified Ideograph character/index.', $match);
      }
    }

    return true;
  }

  /**
   * When a story is saved, substitute {n} with {c} where c is the UTF-8
   * character for the kanji/hanzi, and n is the frame number.
   *
   * This is also done for both public and private stories, in case the author
   * selects a different index later and the frame number reference would be
   * incorrect.
   */
  private function substituteKanjiLinks($text)
  {
    $text = preg_replace_callback('/{([0-9]+)}/', array($this, 'substituteKanjiLinkCallback'), $text);

    return $text;
  }

  public static function substituteKanjiLinkCallback($matches)
  {
    $frameNr = (int)$matches[1]; // frame number or extended (ucs code)
    $kanji = rtkIndex::getCharForIndex($frameNr);

    return sprintf('{%s}', $kanji !== null ? $kanji : $frameNr);
  }
}

