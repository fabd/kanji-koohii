<?php

class uxActions extends sfActions
{
  public function executeIndex()
  {
    $this->guard();
  }

  private function guard()
  {
    $user = $this->getUser();

    // developer only
    $this->forward404Unless(
      $user->getUserName() === 'fuaburisu' || $user->isAdministrator()
    );

    $this->setLayout('fullscreenLayout');
  }
}
