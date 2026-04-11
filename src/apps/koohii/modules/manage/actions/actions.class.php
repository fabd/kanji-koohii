<?php
/**
 * Flashcard Management
 * 
 */

class manageActions extends sfActions
{
  const
    /**
     * Name of the remove flashcards list selection.
     */
    REMOVE_FLASHCARDS = 'removeFlashcards';
  
  public function executeIndex($request)
  {
  }
  
  public function executeAddorder($request)
  {
    // handle ajax requests (POST)
    if ($request->getMethod()===sfRequest::POST)
    {
      $tron = new JsTron();
      $tron->setHtml($this->getPartial('AddOrder'));
      return $this->renderJson($tron);
    }
  }

  public function executeAddOrderConfirm($request)
  {
    $validator = new coreValidator('AddOrder');
    if ($validator->validate($request->getParameterHolder()->getAll()))
    {
      // create a Heisig flashcard selection
      $fcSel = new rtkFlashcardSelection($request);    
      if ($fcSel->addHeisigRange(kk_get_user()->getUserId(), $request->getParameter('txtSelection'))!==false)
      {
        // store valid selection in session
        $newCards = $fcSel->getCards();
        if (count($newCards))
        {
          kk_get_user()->setAttribute('selection', serialize($newCards));
        }

        $tron = new JsTron();
        $tron->setHtml($this->getPartial('AddOrderConfirm', [
          'newCards' => $newCards,
          'countNewCards' => count($newCards)
        ]));
        return $this->renderJson($tron);
      }
      else
      {
        $request->setError('x', 'Invalid selection.');
      }
    }
    
    $this->forward('manage', 'addorder');
  }

  public function executeAddOrderProcess($request)
  {
    // cancel action
    $this->forwardIf($request->hasParameter('cancel'), 'manage', 'addorder');

    // reset: restart form with cleared values
    if ($request->hasParameter('reset'))
    {
      $request->setParameter('txtSelection', '');
      $this->forward('manage', 'addorder');
    }

    // get validated selection from session
    $selection = kk_get_user()->getAttribute('selection');
    $this->forwardIf(!$selection, 'manage', 'addorder');
    kk_get_user()->getAttributeHolder()->remove('selection');
    $newCards = unserialize($selection);

    $cards = ReviewsPeer::addSelection(kk_get_user()->getUserId(), $newCards);

    // count should be identical at this point since duplicates are removed
    // during the confirmation step
    if (count($cards) !== count($newCards))
    {
      $request->setError('x', 'Oops! An error occured while adding flashcards, not all flashcards could be added.');
    }

    $tron = new JsTron();
    $tron->setHtml($this->getPartial('AddOrderProcess', [
      'cards' => $cards,
      'count' => count($cards)
    ]));
    return $this->renderJson($tron);
  }

  public function executeAddcustom($request)
  {
    // handle ajax requests (POST)
    if ($request->getMethod()===sfRequest::POST)
    {
      $tron = new JsTron();
      $tron->setHtml($this->getPartial('AddCustom'));
      return $this->renderJson($tron);
    }
  }

  public function executeAddCustomConfirm($request)
  {
    $validator = new coreValidator('AddCustom');
    if ($validator->validate($request->getParameterHolder()->getAll()))
    {
      // create flashcard selection from string    
      $fcSel = new rtkFlashcardSelection($request);    
      if ($fcSel->setFromString($request->getParameter('txtSelection')))
      {
        $newCards = ReviewsPeer::filterExistingCards(kk_get_user()->getUserId(), $fcSel->getCards());
        $countNewCards = count($newCards);
        $countExistCards = $fcSel->getNumCards() - $countNewCards;
        
        // limit how many cards can be added at once so we dont serialize too much data
        if ($countNewCards > 3030) {
          $request->setError('x', 'Can not add more than 3030 flashcards at once.');
          $this->forward('manage', 'addcustom');
        }

        // store valid selection in session
        if ($countNewCards)
        {
          kk_get_user()->setAttribute('selection', serialize($newCards));
        }

        $tron = new JsTron();
        $tron->setHtml($this->getPartial('AddCustomConfirm', [
          'newCards' => $newCards,
          'countNewCards' => $countNewCards,
          'countExistCards' => $countExistCards
        ]));
        return $this->renderJson($tron);
      }
      else
      {
        $request->setError('x', 'Invalid selection.');
      }
    }
    
    $this->forward('manage', 'addcustom');
  }
  
  public function executeAddCustomProcess($request)
  {
    // cancel: go back to edited form
    $this->forwardIf($request->hasParameter('cancel'), 'manage', 'addcustom');

    // reset: restart form with cleared values
    if ($request->hasParameter('reset'))
    {
      $request->setParameter('txtSelection', '');
      $this->forward('manage', 'addcustom');
    }

    // get validated selection from session
    $selection = kk_get_user()->getAttribute('selection');
    $this->forwardIf(!$selection, 'manage', 'addcustom');
    kk_get_user()->getAttributeHolder()->remove('selection');
    $newCards = unserialize($selection);

    $cards = ReviewsPeer::addSelection(kk_get_user()->getUserId(), $newCards);
    if (count($cards) != count($newCards))
    {
      $request->setError('x', 'Oops! An error occured while adding flashcards, not all flashcards could be added.');
    }
    
    $tron = new JsTron();
    $tron->setHtml($this->getPartial('AddCustomProcess', [
      'cards' => $cards,
      'count' => count($cards)
    ]));
    return $this->renderJson($tron);
  }

  public function executeEditkeywords($request)
  {
    $this->tplEditKeywordUri = $this->getController()->genUrl('study/editkeyword', true);
  }
  
  public function executeEditKeywordsTable($request)
  {
    $tron = new JsTron();
    $tron->setHtml($this->getComponent('manage', 'EditKeywordsTable'));
    return $this->renderJson($tron);
  }

  public function executeRemovelist($request)
  {
    // handle ajax requests (POST)
    if ($request->getMethod()===sfRequest::POST)
    {
      // reset: restart form with empty selection
      if ($request->hasParameter('reset'))
      {
        uiSelectionState::clearSelection(self::REMOVE_FLASHCARDS);
      }
 
      $tron = new JsTron();      
      $tron->setHtml($this->getPartial('RemoveList'));
      return $this->renderJson($tron);
    }
    else
    {
      // reset selection on page load
      uiSelectionState::clearSelection(self::REMOVE_FLASHCARDS);      
    }
  }

  public function executeRemoveListTable($request)
  {
    uiSelectionState::updateSelection(self::REMOVE_FLASHCARDS, 'rf', $request->getParameterHolder()->getAll());

    $tron = new JsTron();
    $tron->setHtml($this->getComponent('manage', 'RemoveListTable'));
    return $this->renderJson($tron);
  }

  public function executeRemoveListConfirm($request)
  {
    // Clear selection > reset form
    $this->forwardIf($request->hasParameter('reset'), 'manage', 'removelist');

    uiSelectionState::updateSelection(self::REMOVE_FLASHCARDS, 'rf', $request->getParameterHolder()->getAll());

    $cards = uiSelectionState::getSelection(self::REMOVE_FLASHCARDS)->getAll();

    $tron = new JsTron();
    $tron->setHtml($this->getPartial('RemoveListConfirm', [
      'cards' => $cards,
      'count' => count($cards)
    ]));
    return $this->renderJson($tron);
  }

  public function executeRemoveListProcess($request)
  {
    // Confirm > cancel > go back and keep the current selection
    $this->forwardIf($request->hasParameter('cancel'), 'manage', 'removelist');
    // Process > continue > reset form
    $this->forwardIf($request->hasParameter('reset'), 'manage', 'removelist');

    // delete selected flashcards
    $selectedCards = uiSelectionState::getSelection(self::REMOVE_FLASHCARDS)->getAll();
    
    $cards = ReviewsPeer::deleteSelection(kk_get_user()->getUserId(), $selectedCards);

    if ($cards === false)
    {
      $request->setError('x', 'Oops! An error occured while deleting flashcards, not all flashcards were deleted.');
    }

    $tron = new JsTron();
    $tron->setHtml($this->getPartial('RemoveListProcess', [
      'cards' => $cards,
      'count' => count($cards)
    ]));
    return $this->renderJson($tron);
  }

  public function executeRemovecustom($request)
  {
    // handle ajax requests (POST)
    if ($request->getMethod()===sfRequest::POST)
    {
      $tron = new JsTron();
      $tron->setHtml($this->getPartial('RemoveCustom'));
      return $this->renderJson($tron);
    }
  }

  public function executeRemoveCustomConfirm($request)
  {
    $validator = new coreValidator('RemoveCustom');
    if ($validator->validate($request->getParameterHolder()->getAll()))
    {
      // create flashcard selection from string    
      $fcSel = new rtkFlashcardSelection($request);
      if ($fcSel->setFromString($request->getParameter('txtSelection')))
      {
        // store valid selection in session
        $cards = $fcSel->getCards();
        if (count($cards))
        {
          kk_get_user()->setAttribute('selection', serialize($cards));
        }

        $tron = new JsTron();
        $tron->setHtml($this->getPartial('RemoveCustomConfirm', [
          'cards' => $cards,
          'count' => count($cards)
        ]));
        return $this->renderJson($tron);
      }
      else
      {
        $request->setError('x', 'Invalid selection.');
      }
    }
    
    $this->forward('manage', 'removecustom');
  }
  
  public function executeRemoveCustomProcess($request)
  {
    // cancel: go back to edited form
    $this->forwardIf($request->hasParameter('cancel'), 'manage', 'removecustom');

    // reset: restart form with cleared values
    if ($request->hasParameter('reset'))
    {
      $request->setParameter('txtSelection', '');
      $this->forward('manage', 'removecustom');
    }

    // delete selected flashcards
    $selection = kk_get_user()->getAttribute('selection');
    $this->forwardIf(!$selection, 'manage', 'removecustom');
    kk_get_user()->getAttributeHolder()->remove('selection');
    $selectedCards = unserialize($selection);

    $cards = ReviewsPeer::deleteSelection(kk_get_user()->getUserId(), $selectedCards);
    if ($cards === false)
    { 
      $request->setError('x', 'Oops! An error occured while deleting flashcards, not all flashcards were deleted.');
    }

    $tron = new JsTron();
    $tron->setHtml($this->getPartial('RemoveCustomProcess', [
      'cards' => $cards,
      'count' => count($cards)
    ]));
    return $this->renderJson($tron);
  }

  public function executeImportKeywords($request)
  {
    if ($request->getMethod()===sfRequest::POST)
    {
      // validate
      if ($request->hasParameter('txtData'))
      {
        $txtData = strip_tags(trim($request->getParameter('txtData')));
        
        $keywords = new rtkImportKeywords($request);
        
        if (false !== ($parsed = $keywords->parse($txtData)) && $keywords->validate($parsed))
        {
          kk_get_user()->setAttribute('keywords', serialize($keywords));

          $tron = new JsTron();
          $tron->setHtml($this->getPartial('ImportKeywordsConfirm', ['keywords' => $keywords]));
          return $this->renderJson($tron);
        }
      }

      $tron = new JsTron();
      $tron->setHtml($this->getPartial('ImportKeywords'));
      return $this->renderJson($tron);
    }
  }
  
  public function executeImportKeywordsConfirm($request)
  {
    // cancel
    $this->forwardIf($request->hasParameter('cancel'), 'manage', 'importKeywords');

    // confirmed
    $tron = new JsTron();
    $tron->setHtml($this->getPartial('ImportKeywordsProcess'));
    return $this->renderJson($tron);
  }

  public function executeImportKeywordsProcess($request)
  {
    // cancel
    $this->forwardIf($request->hasParameter('cancel'), 'manage', 'importKeywords');

    // do the import now
    $keywords = kk_get_user()->getAttribute('keywords');
    $this->forwardIf(!$keywords, 'manage', 'importKeywords');
    $keywordList = unserialize($keywords);
    
    // cleanup
    kk_get_user()->getAttributeHolder()->remove('keywords');

    // errors added to the request
    CustkeywordsPeer::importList(kk_get_user()->getUserId(), $keywordList->getKeywords(), $request);

    /*
    if ($numSuccess < $keywordList->getCount())
    {
      $request->setError('x', 'Oops, one or more items may not be imported succesfully.');
    }
    */

    $tron = new JsTron();
    $tron->setHtml($this->getPartial('ImportKeywordsProcess', [
      'importCount'  => $keywordList->getCount()
    ]));
    return $this->renderJson($tron);
  }
  
  /**
   * Export the user's flaschards with their review status.
   * 
   */
  public function executeExport()
  {
  }
  
  public function executeExportflashcards()
  {
    $response = $this->getResponse();
    $response->setContentType('text/plain; charset=utf-8');

    $throttler = new RequestThrottler(kk_get_user(), 'export');
    if (!$throttler->isValid()) {
      return $this->renderPartial('misc/requestThrottleError');
    }

    $db      = kk_get_database();
    $csv     = new ExportCSV($db);
    $select  = ReviewsPeer::getSelectForExport(kk_get_user()->getUserId());

    $fetchMode   = $db->setFetchMode(coreDatabase::FETCH_NUM);
    $tabularData = $db->fetchAll($select);
    $db->setFetchMode($fetchMode);

    $csvText = $csv->export(
      $tabularData,
      // column names
      ['FrameNumber', _CJ_U('kanji'), 'Keyword', 'LastReview', 'ExpireDate', 'LeitnerBox', 'FailCount', 'PassCount', 'Vocab'],
      // options
      ['col_escape' => [0, 1, 1, 0, 0, 0, 0, 0, 1], 'column_heads' => true]
    );

    $throttler->setTimeout();

    $this->getResponse()->setFileAttachmentHeaders('rtk_flashcards.csv');
    $this->setLayout(false);
    return $this->renderText($csvText);
  }
}
