<?php
/**
 * uiSelectionState maintains a selection of items between requests.
 *
 * @author     Fabrice Denis
 */
class uiSelectionState
{
  protected static ?rtkUser $user = null;

  protected $items = [];

  public function __construct()
  {
    $this->items = [];
  }

  public function clear()
  {
    $this->items = [];
  }

  /**
   * Update selection with values from request parameters.
   * Hidden input values indicate item ids and selected state.
   *
   * @param mixed $paramName
   * @param mixed $params
   */
  public function update($paramName, $params)
  {
    $pattern = '/^sel_'.$paramName.'-([0-9]{1,10})$/';
    foreach ($params as $param => $value) {
      if (preg_match($pattern, $param, $matches)) {
        $id               = $matches[1];
        $state            = $value;
        $this->items[$id] = $state === '1';
      }
    }
  }

  public function store($name)
  {
    self::getUser()->setAttribute($name, serialize($this));
  }

  /**
   * Checks if an item is selected.
   *
   * @param mixed $id
   *
   * @return mixed Boolean for state, or null if id doesn't exist
   */
  public function getState($id)
  {
    return $this->items[$id] ?? null;
  }

  public function getInputTag($paramName, $id)
  {
    $state     = $this->getState($id);
    $value     = $state === true ? '1' : '0';
    $inputName = 'sel_'.$paramName.'-'.$id;

    return input_hidden_tag($inputName, $value);
  }

  public function getCheckboxTag($paramName, $id)
  {
    $state     = $this->getState($id);
    $inputName = 'chk_'.$paramName.'-'.$id;

    // todo: FIXME!!! problème avec le helper?
    return '<input type="checkbox" class="checkbox" name="'.$inputName.'" '.($state ? 'checked ' : '').'/>';
    //    return checkbox_tag($inputName, $id, $state===true, array('class' => 'checkbox'));
  }

  /**
   * Return the id of all selected items.
   *
   * @return array
   */
  public function getAll()
  {
    $selected = [];
    foreach ($this->items as $id => $state) {
      if ($state) {
        $selected[] = $id;
      }
    }

    return $selected;
  }

  /**
   * Serialize method.
   */
  public function __sleep()
  {
    return ['items'];
  }

  /**
   * Serialize method.
   */
  public function __wakeup() {}

  /**
   * Returns active user session.
   */
  public static function getUser(): rtkUser
  {
    if (self::$user === null) {
      self::$user = kk_get_user();
    }

    return self::$user;
  }

  /**
   * Returns named selection from user session, create a new one if it doesn't exist yet.
   *
   * @param mixed $name
   *
   * @return uiSelectionState
   */
  public static function getSelection($name)
  {
    $selection = self::getUser()->getAttribute($name);

    if (!is_string($selection)) {
      $selection = new uiSelectionState();
    } else {
      $selection = unserialize($selection);
    }

    return $selection;
  }

  /**
   * Update selection with request variable.
   *
   * @param mixed $name
   * @param mixed $paramName
   * @param mixed $params
   */
  public static function updateSelection($name, $paramName, $params)
  {
    $selection = self::getSelection($name);
    $selection->update($paramName, $params);
    $selection->store($name);
  }

  /**
   * Clear selected items.
   *
   * @param mixed $name
   */
  public static function clearSelection($name)
  {
    $selection = self::getSelection($name);
    $selection->clear();
    $selection->store($name);
  }
}
