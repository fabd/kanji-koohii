<?php

class uxActions extends sfActions
{
  public function executeIndex()
  {
    $user = $this->getUser();

    // developer only
    $this->forward404Unless(
      $user->getUserName() === 'fuaburisu' || $user->isAdministrator()
    );

    $this->setLayout('fullscreenLayout');
  }
}
