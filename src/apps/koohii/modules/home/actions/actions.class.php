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
      return 'Member';
    }
    
    $request->setParameter('isLandingPage', true);

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
      if (0 && KK_ENV_DEV)
      {
        $request->getParameterHolder()->add([
          'username'=>'guest',
          'password'=>'',
          ]
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
    $this->getUser()->signOutAndClearCookie();

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
      $from_name = trim($request->getParameter('name'));
      $from_addr = trim($request->getParameter('email'));
      $message   = trim($request->getParameter('message'));
    
      // remove html tags
      $message = trim(strip_tags($message));

      // quick fix vs XSS attacks (June 29, 2014)
      if (!preg_match('/^[a-zA-Z0-9 _\'-()]+$/', $from_name) > 0)
      {
        $request->setError('woops', 'Name: please use only letters a-z A-Z \' _ - ( and )');
        return;
      }

      // add the IP address to detect trolls/spammers
      $pathArray   = sfContext::getInstance()->getRequest()->getPathInfoArray();

      // fabd: help identify spam bots (March 2024 - not useful atm)
      // $remote_addr = $pathArray['REMOTE_ADDR'];
      // $message     = 'IP address: '.$remote_addr."\n\n".$message;

      // (fabd) we need a reply-to we can copy, because free SMTP with GMail
      //   does not allow using a custom from address
      $formatReplyTo = rtkMail::formatAddress([
        'name' => $from_name,
        'email' => $from_addr,
      ]);
      $messageHeader = <<<END
REPLY-TO: {$formatReplyTo}
END;
      $message = "$messageHeader\n\n$message";

      // fabd: spam prevention for unauthenticated users
      //  refuse message with links for non-authenticated users
      if (!$this->getUser()->isAuthenticated())
      {
        if (preg_match('#https?://#', $message))
        {
          $request->setError('spam', 'Note: due to spam, we have to block messages containing links. (Pssst! If you are a real person, simply remove the http prefix from the URL and it will go through.)');
          $this->getResponse()->setStatusCode(403);

          $sfs = StopForumSpam::getInstance();
          $sfs->logActivity($remote_addr, '/contact : blocked message with link (403)');

          return $this->renderText('Spam.');
        }

        if ($this->isRussianText($message))
        {
          $request->setError('spam', 'Spam.');
          $this->getResponse()->setStatusCode(403);

          $sfs = StopForumSpam::getInstance();
          $sfs->logActivity($remote_addr, '/contact : blocked Russian (403)');

          return $this->renderText('Spam.');
        }
      }

      if (!KK_ENV_DEV)
      {
        try
        {
          $mailer = new rtkMail();
          $mailer->sendFeedbackMessage(
            'Message from '.$from_name,
            $from_addr,
            "$from_name via Kanji Koohii",
            $message
          );
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
   * Detect Russian in message (at least N characters)
   *  
   * @return boolean
   */
  private function isRussianText($text)
  {
    $count = (int) preg_match_all('/[А-Яа-яЁё]/u', $text);
    return $count > 10;
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

  /**
   * Rss Feed
   *
   * - cache a component because I don't think a cached action remembers the content type
   * - feed cache time is in /config/cache.yml
   *   (technically could be infinite, since we invalidate when add/update a blog post)
   *
   * The feed cache is invalidated in news/actions/
   *
   *   ManageSfCache::clearCacheWildcard('home', '_RssFeed');
   * 
   */
  public function executeRssfeed($request)
  {
    $response = $this->getResponse();
    $response->setContentType('application/rss+xml; charset=UTF-8');

    // sf_cache_key doesn't matter, there is only one feed
    $responseXML = $this->getComponent('home', 'RssFeed', ['sf_cache_key' => 1]);

    return $this->renderText($responseXML);
  }
}
