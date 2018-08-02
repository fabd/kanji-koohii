<?php
class homeActions extends sfActions
{
  /**
   * Home page.
   * 
   * @return 
   */
  public function executeIndex($request)
  {
    if ($this->getUser()->isAuthenticated())
    {    
      // get member stats for quick summary
      $this->progress = rtkIndex::getProgressSummary();
      $this->countExpired = ReviewsPeer::getCountExpired($this->getUser()->getUserId());
      $this->countFailed = ReviewsPeer::getRestudyKanjiCount($this->getUser()->getUserId());

      $request->setParameter('_landingPage', 0);

      return 'Member';
    }
    
    $request->setParameter('_landingPage', 1);
 //   $this->setLayout('landingpageLayout');
    return 'Guest';
  }

  /**
   * Sign In form.
   * 
   * @return 
   */
  public function executeLogin($request)
  {
    $request->setParameter('_homeFooter', true);

    if ($request->getMethod() != sfRequest::POST)
    {
      // get the referer option from redirectToLogin()
      $referer = $this->getUser()->getAttribute('login_referer', '');

      // get other options from redirectToLogin()
      $username = $this->getUser()->getAttribute('login_username', '');

      // clear redirectToLogin() options
      $this->getUser()->getAttributeHolder()->remove('login_referer');
      $this->getUser()->getAttributeHolder()->remove('login_username');

      $this->getRequest()->setParameter('referer', empty($referer) ? '@homepage' : $referer);
      $this->getRequest()->setParameter('username', $username);

      // AUTO FILL FORM (DEVELOPMENT ONLY!)
      if (0 && CORE_ENVIRONMENT === 'dev')
      {
        $request->getParameterHolder()->add(array(
          'username'=>'guest',
          'password'=>'',
          )
        );
      }
    }
    else
    {
      $validator = new coreValidator($this->getActionName());
      
      if ($validator->validate($request->getParameterHolder()->getAll()))
      {
        $username = trim($request->getParameter('username'));
        $raw_password = trim($request->getParameter('password'));
        $rememberme = $request->hasParameter('rememberme');

        // check that user exists and password matches
        $user = UsersPeer::getUser($username);
        if (!$user || ($this->getUser()->getSaltyHashedPassword($raw_password) != $user['password']) )
        {
          $request->setError('login_invalid', "Invalid username and/or password.");
          return;
        }

        // sign in user
        $this->getUser()->signIn($user);

        // optionally, create the remember me cookie
        if ($rememberme)
        {
          $this->getUser()->setRememberMeCookie($user['username'], $this->getUser()->getSaltyHashedPassword($raw_password));
        }
        
        // succesfully signed in
        $referer = $this->getRequestParameter('referer', ''); 
        return $this->redirect( empty($referer) ? '@homepage' : $referer );  //FIXME referer shouldn't be empty if present
      }
    }
  }

  /**
   * Sign Out.
   * 
   * @return 
   */
  public function executeLogout($request)
  {
    $this->getUser()->signOut();
    
    // clear the rememberme cookie
    $this->getUser()->clearRememberMeCookie();
    
    return $this->redirect('@homepage');
  }
  
  /**
   * Contact/Feedback Form page.
   * 
   */
  public function executeContact($request)
  {
    if ($request->getMethod() != sfRequest::POST)
    {
      return;
    }

    $validator = new coreValidator($this->getActionName());

    if ($validator->validate($request->getParameterHolder()->getAll()))
    {
      $name_from = trim($request->getParameter('name'));
      $reply_to  = trim($request->getParameter('email'));
      $message   = trim($request->getParameter('message'));

      // quick fix vs XSS attacks (June 29, 2014)
      if (!preg_match('/^[a-zA-Z0-9 _\'-()]+$/', $name_from) > 0)
      {
        $request->setError('woops', 'Name: please use only letters a-z A-Z \' _ - ( and )');
        return;
      }

      // add some context information
      $message = "Website: Kanji Koohii!\n\n" . $message;

      // add the IP address to detect trolls/spammers
      $pathArray   = sfContext::getInstance()->getRequest()->getPathInfoArray();

      // fabd: help identify spam bots
      $remote_addr = $pathArray['REMOTE_ADDR'];
      $message     = 'IP address: '.$remote_addr."\n\n".$message;

      // fabd: spam prevention, refuse message with links for non-authenticated users
      if (!$this->getUser()->isAuthenticated() && preg_match('#https?://#', $message)) {
        $request->setError('spam', 'Note: due to spam, we have to block messages containing links. (Pssst! If you are a real person, simply remove the http prefix from the URL and it will go through.)');
        $this->getResponse()->setStatusCode(403);

        $sfs = new StopForumSpam();
        //$regip = StopForumSpam::getRemoteAddress();
        $sfs->logActivity($remote_addr, '/contact : blocked message with link (403)');

        return;
      }

      if (CORE_ENVIRONMENT !== 'dev')
      {
        try
        {
          $mailer = new rtkMail();
          $mailer->sendFeedbackMessage('Feedback from '.$name_from, $reply_to, $name_from, $message);
        }
        catch(sfException $e)
        {
          $request->setError('smtp_mail', "I'm sorry, there was a problem sending the email. "
                                          ."Please try again shortly.");
          return;
        }
      }

      return 'EmailSent';
    }
  }
  
  /**
   * Display the active members list.
   *
   */
  public function executeMemberslist($request)
  {
    ActiveMembersPeer::deleteInactiveMembers();
  }

  /**
   * Active members list table ajax update.
   * 
   * @return 
   */
  public function executeMemberslisttable($request)
  {
    $tron = new JsTron();
    return $tron->renderComponent($this, 'home', 'MembersList');
  }
}
