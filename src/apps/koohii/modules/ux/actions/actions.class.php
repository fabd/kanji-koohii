<?php

class uxActions extends sfActions
{
  public function executeIndex()
  {
    $this->guard();
  }

  private function guard()
  {
    $user = kk_get_user();

    // developer only
    $this->forward404Unless(
      $user->getUserName() === 'fuaburisu' || $user->isAdministrator()
    );

    $this->setLayout('fullscreenLayout');
  }
}
