<?php
class defaultActions extends sfActions
{
  /**
   * Executes index action
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('default', 'module');
  }
  
  /**
   * Error page for page not found (404) error
   *
   */
  public function executeError404()
  {
  }
  
  /**
   * Warning page for restricted area - requires login
   *
   */
  public function executeSecure()
  {
  }

  /**
   * Redirects <website url>/admin to the backend app.
   * 
   * @see  Url routing @go_to_backend
   */  
  public function executeGoToBackend()
  {
    $this->redirect(sfConfig::get('app_backend_url'));
  }
}
