<?php
/**
 * Various helpers to build forms, based on the FormHelper in the early version
 * of symfony (that seems to be absent from sf 1.4).
 *
 * Repopulating forms:
 *   The text input, checkbox, radio and textarea elements can be repopulated.
 *   For these elements, the value argument is a default value. When the field is repopulated,
 *   the get/post value is used in place of the default value.
 *   
 *   Checkbox and radios must use the array name syntax otherwise the default values are ignored.
 * 
 *   If an error exists in the request with the name matching one of the element's name,
 *   a css class "error" is added.
 * 
 * @author     Fabrice Denis
 * @copyright  Code based on Symfony php framework, by Fabien Potencier (www.symfony-project.org)
 */

/**
 * Returns a formatted set of <option> tags based on optional <i>$options</i> array variable.
 *
 * The options_for_select helper is usually called in conjunction with the select_tag helper, as it is relatively
 * useless on its own. By passing an array of <i>$options</i>, the helper will automatically generate <option> tags
 * using the array key as the value and the array value as the display title. Additionally the options_for_select tag is
 * smart enough to detect nested arrays as <optgroup> tags.  If the helper detects that the array value is an array itself,
 * it creates an <optgroup> tag with the name of the group being the key and the contents of the <optgroup> being the array.
 *
 * <b>Options:</b>
 * - include_blank  - Includes a blank <option> tag at the beginning of the string with an empty value
 * - include_custom - Includes an <option> tag with a custom display title at the beginning of the string with an empty value
 *
 * <b>Examples:</b>
 * <code>
 *  echo select_tag('person', options_for_select(array(1 => 'Larry', 2 => 'Moe', 3 => 'Curly')));
 * </code>
 *
 * <code>
 *  $card_list = array('VISA' => 'Visa', 'MAST' => 'MasterCard', 'AMEX' => 'American Express', 'DISC' => 'Discover');
 *  echo select_tag('cc_type', options_for_select($card_list, 'AMEX', array('include_custom' => '-- Select Credit Card Type --')));
 * </code>
 *
 * <code>
 *  $optgroup_array = array(1 => 'Joe', 2 => 'Sue', 'Group A' => array(3 => 'Mary', 4 => 'Tom'), 'Group B' => array(5 => 'Bill', 6 =>'Andy'));
 *  echo select_tag('employee', options_for_select($optgroup_array, null, array('include_blank' => true)), array('class' => 'mystyle'));
 * </code>
 *
 * @param  array dataset to create <option> tags and <optgroup> tags from
 * @param  string selected option value
 * @param  array  additional HTML compliant <option> tag parameters
 * @return string populated with <option> tags derived from the <i>$options</i> array variable
 * @see select_tag
 */
function options_for_select($options = [], $selected = '', $html_options = [])
{
  $html_options = _parse_attributes($html_options);

  if (is_array($selected))
  {
    $selected = array_map('strval', array_values($selected));
  }

  $html = '';

  if ($value = _get_option($html_options, 'include_custom'))
  {
    $html .= content_tag('option', $value, ['value' => ''])."\n";
  }
  else if (_get_option($html_options, 'include_blank'))
  {
    $html .= content_tag('option', '', ['value' => ''])."\n";
  }

  foreach ($options as $key => $value)
  {
    if (is_array($value))
    {
      $html .= content_tag('optgroup', options_for_select($value, $selected, $html_options), ['label' => $key])."\n";
    }
    else
    {
      $option_options = ['value' => $key];

      if (
          (is_array($selected) && in_array(strval($key), $selected, true))
          ||
          (strval($key) == strval($selected))
         )
      {
        $option_options['selected'] = 'selected';
      }

      $html .= content_tag('option', $value, $option_options)."\n";
    }
  }

  return $html;
}

/**
 * Returns a <select> tag, optionally comprised of <option> tags.
 *
 * The select tag does not generate <option> tags by default.  
 * To do so, you must populate the <i>$option_tags</i> parameter with a string of valid HTML compliant <option> tags.
 * Fortunately, Symfony provides a handy helper function to convert an array of data into option tags (see options_for_select). 
 * If you need to create a "multiple" select tag (ability to select multiple options), set the <i>multiple</i> option to true.  
 * Doing so will automatically convert the name field to an array type variable (i.e. name="name" becomes name="name[]").
 * 
 * <b>Options:</b>
 * - multiple - If set to true, the select tag will allow multiple options to be selected at once.
 *
 * <b>Examples:</b>
 * <code>
 *  $person_list = array(1 => 'Larry', 2 => 'Moe', 3 => 'Curly');
 *  echo select_tag('person', options_for_select($person_list, $sf_params->get('person')), array('class' => 'full'));
 * </code>
 *
 * <code>
 *  echo select_tag('department', options_for_select($department_list), array('multiple' => true));
 * </code>
 *
 * <code>
 *  echo select_tag('url', options_for_select($url_list), array('onChange' => 'Javascript:this.form.submit();'));
 * </code>
 *
 * @param  string field name 
 * @param  mixed contains a string of valid <option></option> tags, or an array of options that will be passed to options_for_select
 * @param  array  additional HTML compliant <select> tag parameters
 * @return string <select> tag optionally comprised of <option> tags.
 * @see options_for_select, content_tag
 */
function select_tag($name, $option_tags = null, $options = [])
{
  $options = _convert_options($options);
  $id = $name;
  if (isset($options['multiple']) && $options['multiple'] && substr($name, -2) !== '[]')
  {
    $name .= '[]';
  }
  if (is_array($option_tags))
  {
    $option_tags = options_for_select($option_tags);
  }

  return content_tag('select', $option_tags, array_merge(['name' => $name/*, 'id' => get_id_from_name($id)*/], $options));
}

/**
 * Create an <input type="text" ... /> element.
 * 
 * @param string Name attribute
 * @param mixed  Default value
 * @param array   Optional attributes
 */
function input_tag($name, $value = null, $options = [])
{
  // repopulate with get/post data
  $_request = sfContext::getInstance()->getRequest();
  $value = $_request->getParameter($name, $value);

  // add css class
  $options = _parse_attributes($options);

  $options = array_merge(['type' => 'text', 'name' => $name, /*'id' => get_id_from_name($name, $value),*/ 'value' => $value], $options);
  _check_field_error($name, $options);
  return tag('input', _convert_options($options));
}

/**
 * Create an <input type="hidden" ... /> element.
 * 
 * @param string Name attribute
 * @param mixed  Value attribute
 * @param array   Optional attributes
 */
function input_hidden_tag($name, $value = null, $options = [])
{
  // repopulate with get/post data
  $_request = sfContext::getInstance()->getRequest();
  $value = $_request->getParameter($name, $value);

  $options = array_merge(['type' => 'hidden', 'name' => $name, 'value' => $value], _parse_attributes($options));
  return tag('input', _convert_options($options));
}

/**
 * Create an <input type="password" ... /> element.
 * 
 * @param string Name attribute
 * @param mixed  Value
 * @param array   Optional attributes
 */
function input_password_tag($name, $value = null, $options = [])
{
  $_request = sfContext::getInstance()->getRequest();
  $value = $_request->getParameter($name, $value);

  // add css class
  $options = _parse_attributes($options);

  $options = array_merge(['type' => 'password', 'name' => $name, /*'id' => get_id_from_name($name),*/ 'value' => $value], $options);
  _check_field_error($name, $options);
  return tag('input', _convert_options($options));
}

/**
 * Create a <textarea> element, with content.
 * 
 * @param string Name attribute
 * @param mixed  Default content
 * @param array   Optional attributes
 */
function textarea_tag($name, $content = null, $options = [])
{
  $_request = sfContext::getInstance()->getRequest();

  // repopulate with get/post data
  $content = $_request->getParameter($name, $content);

  // add css class
  $options = _parse_attributes($options);

  $options = array_merge(['name' => $name/*, 'id' => get_id_from_name($name)*/], $options);

  _check_field_error($name, $options);
  
  return content_tag('textarea', escape_once((is_object($content)) ? $content->__toString() : $content), _convert_options($options));
}

/**
 * Create a checkbox.
 * 
 * For mutliple checkboxes in the same group the name should be an array (eg. "choice[]")
 * otherwise the field won't repopulate correctly.
 * 
 * @todo  Repopulation doesn't work when no checkbox are selected (it will use the default value).
 * 
 * @param string  Name attribute
 * @param string  Value attribute
 * @param boolean Default checked state
 * @param array    Optional attributes
 */
function checkbox_tag($name, $value = '1', $checked = false, $options = [])
{
  $options = array_merge(['type' => 'checkbox', 'name' => $name, /*'id' => get_id_from_name($name, $value),*/ 'value' => $value], _parse_attributes($options));

  // repopulate field
  $checked = _repopulate_input_cb($name, $value, $checked);
  
  if ($checked) {
    $options['checked'] = 'checked';
  }
  return tag('input', _convert_options($options));
}

/**
 * Create a radio button.
 * 
 * For mutliple radio buttons in the same group the name should be an array (eg. "choice[]")
 * otherwise the field won't repopulate correctly.
 * 
 * @param string  Name attribute
 * @param string  Value attribute
 * @param boolean Default checked state
 * @param array    Optional attributes
 */
function radiobutton_tag($name, $value = '1', $checked = false, $options = [])
{
  $options = array_merge(['type' => 'radio', 'name' => $name, /*'id' => get_id_from_name($name, $value),*/ 'value' => $value], _parse_attributes($options));

  // repopulate field
  $checked = _repopulate_input_cb($name, $value, $checked);

  if ($checked) {
    $options['checked'] = 'checked';
  }
  return tag('input', _convert_options($options));
}

/**
 * Returns an XHTML compliant <input> tag with type="submit".
 * 
 * By default, this helper creates a submit tag with a name of <em>commit</em> to avoid
 * conflicts with other parts of the framework.  It is recommended that you do not use the name
 * "submit" for submit tags unless absolutely necessary. Also, the default <i>$value</i> parameter
 * (title of the button) is set to "Save changes", which can be easily overwritten by passing a 
 * <i>$value</i> parameter.
 *
 * <b>Examples:</b>
 * <code>
 *  echo submit_tag();
 * </code>
 *
 * <code>
 *  echo submit_tag('Update Record');
 * </code>
 *
 * @param  string Field value (title of submit button)
 * @param  array  Additional HTML compliant <input> tag parameters
 * @return string XHTML compliant <input> tag with type="submit"
 */
function submit_tag($value = 'Save changes', $options = [])
{
  return tag('input', array_merge(['type' => 'submit', 'name' => 'commit', 'value' => $value], _convert_options($options)));
}

/**
 * Returns a <label> tag with <i>$label</i> for the specified <i>$id</i> parameter.
 *
 * @param  string id
 * @param  string label or title
 * @param  array  additional HTML compliant <label> tag parameters
 * @return string <label> tag with <i>$label</i> for the specified <i>$id</i> parameter.
 */
function label_for($id, $label, $options = [])
{
  $options = _parse_attributes($options);

  if (is_object($label) && method_exists($label, '__toString'))
  {
    $label = $label->__toString();
  }

  return content_tag('label', $label, array_merge(['for' => get_id_from_name($id, null)], $options));
}

/**
 * Add a css class "error" to the options for rendering the form element,
 * if a validation error exists for the given element's name.
 * 
 * If the class attribute is already specified, existing classes are maintained.
 * 
 * @param string  Element name attribute
 * @param array   Assoc. array of options for the tag
 */
function _check_field_error($name, &$options)
{
  if (sfContext::getInstance()->getRequest()->hasError($name))
  {
    $css_class = array_key_exists('class', $options) ? $options['class'].' ' : '';
    $css_class = $css_class . 'error';
    $options['class'] = $css_class;
  }
}

/**
 * Repopulate the "checked" state for a checkbox/radio button.
 * 
 * @param string  Name attribute of the input
 * @param string  Value attribute of the input
 * @param boolean Default state for the checkbox/radio
 *
 * @return boolean Checkbox state repopulated, or the default value.
 */
function _repopulate_input_cb($name, $value, $checked)
{
  $_request = sfContext::getInstance()->getRequest();
  if (strstr($name, '[]'))
  {
    if ( ($array_values = $_request->getParameter(rtrim($name,'[]')))!==null )
    {
      assert('is_array($array_values)');
      if (in_array($value, $array_values)) {
        $checked = true;
      }
      else {
        $checked = false;
      }
    }
  }
  else {
    // checkboxes with unique names (no []) repopulate but can not use default values
    $value = $_request->getParameter($name);
    $checked = $value!==null && !empty($value);
  }
  return $checked;  
}
