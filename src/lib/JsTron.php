<?php
/**
 * JsTron is a simple wrapper around JSON messages.
 * See TRON (tron.ts) for the front end side.
 * 
 * Example Tron object:
 * 
 *   {
 *     status: 1,
 *     props: { username: "John" },
 *     errors: ["Password is too short"],  <== OPTIONAL
 *     html: ...                           <== OPTIONAL
 *   }
 * 
 * Implements JsonSerializable, so we can render a JSON message in an
 * action like this:
 * 
 *   $tron = new JsTron();
 *   $tron->set("foo", 123);
 *   $tron->setError('Session expired. Please log in.');
 *   $this->renderJson($tron);
 *
 * Example action that returns a partial in a JSON message:
 *
 *   $tron->setHtml($this->getPartial('templateName', ['foo' => 123]));
 *   return $this->renderJson($tron);
 * 
 * Example action that returns a component in a JSON message:
 * 
 *   $tron->setHtml($this->getComponent('moduleName', 'ComponentName', ['foo' => 123]));
 *   return $this->renderJson($tron);
 * 
 * Methods:
 * 
 *   set($name, $value)          Set one property of the message (props).
 *   add($parameters)            Set multiple properties (props)
 *   setStatus($status)          Set the status ( FAILED, SUCCESS, PROGRESS )
 *   setError($message)          Adds an error message (can be called multiple times)
 *   setHtml($html)              Sets the html property
 *   
 */

class JsTron implements JsonSerializable
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

  protected
    $status      = null,
    $errors      = [],
    $html        = '';

  private array $props = [];

  /**
   * Constructor.
   *
   * Create a JsTron instance, optional properties set on creation.
   *
   * @param  array  $parameters
   *
   * @return void
   */
  public function __construct($parameters = [])
  {
    $this->status = self::STATUS_SUCCESS;

    $this->add($parameters);
  }

  /**
   * Set a single message property.
   *
   * @param  string  $name
   * @param  mixed   $value
   * @return void
   */
  public function set($name, $value)
  {
    $this->props[$name] = $value;
  }

  /**
   * Merge an array of message properties.
   *
   * @param  array  $parameters
   * @return void
   */
  public function add($parameters)
  {
    $this->props = array_merge($this->props, $parameters);
  }

  /**
   * Return all message properties.
   *
   * @return array
   */
  public function getAll(): array
  {
    return $this->props;
  }

  public function setStatus($status)
  {
    assert($status === self::STATUS_FAILED || $status === self::STATUS_SUCCESS || $status === self::STATUS_PROGRESS);
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
    $this->errors[] = $message;
  }

  /**
   * Set errors from the Request errors (eg. from form validation).
   *
   * @param coreRequest $request
   * @return void
   */
  public function setErrorsFromRequest(coreRequest $request)
  {
    $errors = $request->getErrors();
    foreach ($errors as $key => $message) {
      $this->setError($message);
    }
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

  public function jsonSerialize(): mixed
  {
    return $this->getJson();
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
}
