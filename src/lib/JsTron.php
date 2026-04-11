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
 *   $tron->addError('Session expired. Please log in.');
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
 *   addError($message)          Adds an error message (can be called multiple times)
 *   setHtml($html)              Sets the html property
 */
class JsTron implements JsonSerializable
{
  /**
   * Status codes used by App.Helper.TRON (app.js).
   */
  // a form submission contains errors, or a blocker (do not close ajax dialog)
  public const STATUS_FAILED = 0;
  // a form is submitted succesfully, proceed (eg. close ajax dialog)
  public const STATUS_SUCCESS = 1;
  // a form submitted succesfully, and continues with another step
  public const STATUS_PROGRESS = 2;

  private int $status;

  private array $errors = [];

  private ?string $html = null;

  private array $props = [];

  /**
   * Constructor.
   *
   * Create a JsTron instance, optional properties set on creation.
   */
  public function __construct(array $props = [])
  {
    $this->status = self::STATUS_SUCCESS;

    $this->add($props);
  }

  /**
   * Set a single message property.
   */
  public function set(string $name, mixed $value): void
  {
    $this->props[$name] = $value;
  }

  /**
   * Merge an array of message properties.
   */
  public function add(array $props): void
  {
    $this->props = array_merge($this->props, $props);
  }

  /**
   * Return all message properties.
   */
  public function getAll(): array
  {
    return $this->props;
  }

  public function setStatus(int $status): void
  {
    assert($status === self::STATUS_FAILED || $status === self::STATUS_SUCCESS || $status === self::STATUS_PROGRESS);
    $this->status = $status;
  }

  public function getStatus(): int
  {
    return $this->status;
  }

  public function addError(string $message): void
  {
    $this->errors[] = $message;
  }

  public function addErrors(array $errors): void
  {
    foreach ($errors as $key => $message) {
      $this->addError($message);
    }
  }

  public function getErrors(): array
  {
    return $this->errors;
  }

  public function hasErrors(): bool
  {
    return count($this->errors) > 0;
  }

  public function setHtml(string $html): void
  {
    $this->html = $html;
  }

  public function getHtml(): ?string
  {
    return $this->html;
  }

  public function jsonSerialize(): mixed
  {
    $obj = new stdClass();
    $obj->status = $this->getStatus();
    $obj->props = $this->getAll();

    if ($this->html !== null) {
      $obj->html = $this->getHtml();
    }

    if ($this->hasErrors()) {
      $obj->errors = $this->getErrors();
    }

    return $obj;
  }
}
