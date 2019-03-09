<?php
class aboutActions extends sfActions
{
  public function executeIndex()
  {
    $this->forward('about', 'about');
  }

  public function executeAbout()
  {
    $response = $this->getResponse();

    // test et preuve pour HostGator après l'attaque 2014/02/19
    $throttler = new RequestThrottler($this->getUser(), 'baduser');
    $throttler->setInterval(2);
    
    if (!$throttler->isValid())
    {
      $throttler->setTimeout(); // reset le timer

    //  $response->setContentType('text/plain; charset=utf-8');
      $response->setContentType('html');
      return $this->renderPartial('misc/requestThrottleError');
    }

    $throttler->setTimeout();
  }
 
  public function executePhpinfo()
  {
    if ($this->getUser()->isAdministrator()) {
      phpinfo();exit;
    }
    // dont do a 404 + log on staging
    exit;
  }

  public function executeLicense()
  {
  }

  public function executeLearnmore()
  {
  }
  
  public function executeSupport()
  {
  }
}
