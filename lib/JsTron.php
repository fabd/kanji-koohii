<?php
/**
 * Wrapper for JSON communication, extends sfParameterHolder.
 * 
 * See Core.Helper.TRON (lib/front/corejs/core/core-json.js) for the front end side.
 *
 * 
 * Methods:
 *   set($name, $value)          Set properties of the message (cf. sfParameterHolder)
 *   add($parameters)
 *   ...
 *
 *   setStatus($status)
 *   setError($message)
 *   setHtml($html)
 *
 *   render($action)
 *   renderJson($action)         Proxy for render().
 *
 *   renderPartial()             setHtml() with a Symfony partial render
 *   renderComponent()           ... likewise for ....... component render
 *
 *
 * Return a simple SUCCESS message:
 *
 *   $tron = new JsTron();
 *   $tron->setStatus(JsTron::STATUS_SUCCESS);
 *   return $tron->renderJson($this);
 *   
 *
 * Return some data:
 *
 *   $tron = new JsTron(array('foo' => 'bar'));
 *   $tron->setStatus(JsTron::STATUS_SUCCESS);
 *   return $tron->renderJson($this);
 *   
 *   
 * Return a session error to be displayed by client:
 *
 *   $tron = new JsTron(array('login' => true));
 *   $tron->setError('Session expired. Please log in.');
 *   $tron->setStatus(JsTron::STATUS_FAILED);
 *   return $tron->renderJson($this);
 *   
 *
 * @TODO    Strip \n \r \t and whitespace to reduce output.
 *
 * @author  Fabrice Denis
 */

class JsTron extends sfParameterHolder
{
  /**
   * Status codes used by App.Helper.TRON (app.js)
   */
  // a form submission contains errors, or a blocker (do not close ajax dialog)
  const STATUS_FAILED   = 0;
  // a form is submitted succesfully, proceed (eg. close ajax dialog)
  const STATUS_SUCCESS  = 1;
  // a form submitted succesfully, and continues with another step
  const STATUS_PROGRESS = 2;
 
  // must match TRON.CSS_CLASS in app.js, used for html TRON
  const CSS_CLASS = 'JsTRON';

  protected
    $status      = null,
    $errors      = array(),
    $html        = '';

  /**
   * Constructor.
   *
   * Create a JsonWrapper instance, optional properties set on creation.
   * 
   * @param  array  $parameters 
   *
   * @return void
   */
  public function __construct($parameters = array())
  {
    parent::__construct();

    $this->status = self::STATUS_SUCCESS;

    $this->add($parameters);
  }

  public function setStatus($status)
  {
    assert('$status === self::STATUS_FAILED || $status === self::STATUS_SUCCESS || $status === self::STATUS_PROGRESS');
    $this->status = $status;
  }

  public function getStatus()
  {
    if (null === $this->status)
    {
      throw new sfException(__METHOD__.' Status is not set.');
    }
    return $this->status;
  }

  public function setError($message)
  {
    $this->errors = array($message);
  }

  public function getErrors()
  {
    return $this->errors;
  }

  public function hasErrors()
  {
    return count($this->errors) > 0;
  }

  public function setHtml($html)
  {
    $this->html = $html;
  }

  public function getHtml()
  {
    return $this->html;
  }

  /**
   * Returns the JSON message in as a Php object.
   * 
   * @return object
   */
  public function getJson()
  {
    $obj = new stdClass();
    $obj->status = $this->getStatus();
    $obj->props  = $this->getAll();

    if ($this->html !== '')
    {
      $obj->html = $this->getHtml();
    }

    if ($this->hasErrors())
    {
      $obj->errors = $this->getErrors();
    }

    return $obj;
  }
  
  /**
   * Render a JSON response.
   *
   * Use this as the return statement of a symfony action, eg:
   *
   *   return $tron->render($this);
   * 
   * @param  mixed $action 
   *
   * @return coreView::NONE
   */
  private function render($action)
  {
    $json = $this->getJson();
    $text = coreJson::encode($json);
    
    sfContext::getInstance()->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');

    return $action->renderText($text);
  }

  /**
   * Proxy for render().
   * 
   * @param  coreAction $action 
   *
   * @return coreView::NONE
   */
  public function renderJson($action)
  {
    return $this->render($action);
  }
  
  /**
   * Render the TRON response with html set from partial.
   *
   * @param  coreAction $action 
   * @param  string     $partialName    Partial name (same as get_partial() helper)
   * @param  mixed      $vars           Partial vars
   *
   * @return coreView::NONE
   */
  public function renderPartial($action, $partialName, $vars = null)
  {
    $html = $action->getPartial($partialName, $vars);
    $this->setHtml($html);
    return $this->render($action);
  }
  
  /**
   * Render the TRON response with html set from a component.
   * 
   * @param  coreAction $action
   * @param  string     $moduleName     module name
   * @param  string     $componentName  component name
   * @param  array      $vars           vars
   *
   * @return coreView::NONE
   */
  public function renderComponent($action, $moduleName, $componentName, $vars = null)
  {
    $html = $action->getComponent($moduleName, $componentName, $vars);
    $this->setHtml($html);
    return $this->render($action);
  }
}
