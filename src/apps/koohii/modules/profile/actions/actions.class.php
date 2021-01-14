<?php
/**
 * User profile, community features(someday).
 * 
 */

class profileActions extends sfActions
{
  public function executeIndex($request)
  {
    $username = $request->getParameter('username');

    if (!$username)
    {
      if ($this->getUser()->isAuthenticated())
      {
        $username = $this->getUser()->getUserName();
      }
      else
      {
        // if unauthenticated user checks his (bookmarked?) profile, go to login and back
        $url = $this->getController()->genUrl('profile/index', true);
        $this->getUser()->redirectToLogin(['referer' => $url]);
      }
    }

    if (false === ($user = UsersPeer::getUser($username)))
    {
      return sfView::ERROR;
    }

    $profileUser = (object) $user;
    $this->profile_user = $profileUser;
    $this->profile_self = $profileUser->userid === $this->getUser()->getUserId();
  }
}
