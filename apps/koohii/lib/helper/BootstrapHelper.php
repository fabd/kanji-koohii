<?php

/**
 * Helpers to output Bootstrap 3 css/components.
 *
 * OPTIONS
 *
 *   $options     are passed to the Symfony tag helpers, therefore supporting
 *                'class', 'style', and any other custom html attributes.
 *
 * Common Helpers:
 *
 *  _bs_button()                                       MUST add Bootstrap css as option 'class' => 'btn btn-success ...'
 *  _bs_button_with_icon()                             DONT add 'btn btn-success' ... MUST add 'icon' => 'fa-icon-id'
 *
 *
 * Form Helpers:
 *  
 *  _bs_formgroup([array $options], ...)
 *
 *  _bs_input_checkbox($name, $options = array())      ALWAYS: value="1" ... OPTIONAL: 'label' => 'Label text'
 *  _bs_input_text    ($name, $options = array())      ...
 *  _bs_input_email   ($name, $options = array())      ...
 *  _bs_input_password($name, $options = array())      ...
 *
 *  _bs_submit_tag    ($label, $options = array())
 *
 * 
 * Helper helpers (not meant to be directly used):
 *
 *  _bs_input         ($type, $name, $options = array())
 *  
 *  _bs_class_merge()
 * 
 *
 * Form Layout:
 *
 *  INLINE       Add class "form-control-i" to inline elements within _bs_formgroup()
 *
 *
 *
 * See:
 *   http://getbootstrap.com/css/
 * 
 * @author  Fabrice Denis
 */

/**
 * Returns html for a Bootstrap button.
 *
 * Uses 'btn btn-default' unless btn-success, btn-warning, etc specified.
 * 
 * Additional options as per Symfony's link_to() helper: 'absolute', 'query_string', 'anchor', etc.
 *
 */
function _bs_button($name, $internal_uri, array $options = array())
{
  // TODO

  return link_to($name, $internal_uri, $options);
}

/**
 * another helper to help refactoring later
 *
 * Options:
 *   icon    fontawesome icon id (eg. fa-file-o)
 * 
 */
function _bs_button_with_icon($name, $internal_uri, array $options = array())
{
  $iconId = _get_option($options, 'icon');
  assert('$iconId !== null');

  _bs_class_merge($options, 'btn btn-success');

  $name = "<i class=\"fa $iconId\"></i>$name";

  return link_to($name, $internal_uri, $options);
}

/**
 * Classnames are appended to $options['class'] if present.
 * 
 */
function _bs_class_merge(array & $options, $classnames) {
  if (isset($options['class'])) {
    if (!is_array($options['class'])) {
      $classnames = $classnames.' '.$options['class'];
    }
    else {
      throw new sfException('_bs_class_merge() options["class"] must be a string');
    }
  }

  $options['class'] = $classnames;
}


/**
 * Optional first argument : array $options
 *
 * Example:
 *
 *   _bs_form_group(
 *     array('style' => 'color:red'),
 *     '<span>Hello</span>',
 *     _bs_input_text('Label', 'name_and_id')
 *   )
 */
function _bs_form_group() {
  if (func_num_args() < 1) {
    throw new sfException('_bs_form_group() has no content.');
  }

  $args = func_get_args();

  // pull the optional first argument : array $options
  $options = is_array($args[0]) ? array_shift($args) : array();

  _bs_class_merge($options, 'form-group');

  $html = "\n<div "._tag_options($options).'>'
        . implode($args)
        . "\n</div>";

  return $html;
}

function _bs_input($type, $name, $options = array()) {
  $html = array();

  if (null !== ($label = _get_option($options, 'label'))) {
    $html[] = "\n  ".label_for($name /* id */, $label, array('class' => 'control-label'));
  }

  if (null !== ($optional = _get_option($options, 'optional'))) {
    $html[] = '<span class="form-optional">(optional)</span>';
  }

  // input  
  _bs_class_merge($options, 'form-control');

  // FIXME  obsolete FormHelper (not the Symfony one) did not include 'id'
  $options['id'] = get_id_from_name($name);

  if ($type === 'text' ||
      $type === 'email' /* skip the annoying browser-based email checking */ ) {
    $html[] = "\n  ".input_tag($name, '', $options);
  }
  elseif ($type === 'password') {
    $html[] = "\n  ".input_password_tag($name, null, $options);
  }
  else {
    throw new sfException('Unsupported input type in _bs_input()');
  }

  // help text
  if (null !== ($helptext = _get_option($options, 'helptext'))) {
    $html[] = "\n  ".'<span class="help-block">'.$helptext.'</span>';
  }

  return implode($html);
}

/**
 * Note: we diverge from Bootstrap here and output a .form-group for consistency with
 * the form row helper, as well as use a span, so we can use display:table to fix the
 * freakin checkbox/label alignment.
 *
 *  <div class="form-group">
 *    <label><input ...><span>Label text</span>
 *  </div>
 */
function _bs_input_checkbox($name, $options = array()) {
  // we want a wrapping label here
  if (null !== ($label = _get_option($options, 'label'))) {
    $html[] = "\n  <label>";
  }

  $html[] = checkbox_tag($name, '1', false, $options)."<span>$label</span>";

  if (null !== $label) {
    $html[] = "</label>\n";
  }

  return implode($html);
}

function _bs_input_email($name, $options = array()) {
  return _bs_input('text', $name, $options);
}

function _bs_input_password($name, $options = array()) {
  return _bs_input('password', $name, $options);
}

function _bs_input_text($name, $options = array()) {
  return _bs_input('text', $name, $options);
}

function _bs_submit_tag($label, $options = array()) {
  _bs_class_merge($options, 'btn btn-success');
  return submit_tag($label, $options);
}



/**
 * Just a proxy for now to ease refactoring later. Start inline javascriot
 * slot, which is included at end of document *AFTER* the javascript bundles.
 *
 * Put it here for now (included in config/settings.yml default helpers)
 *
 */
function koohii_onload_slot() {
  $name = 'inline_javascript';
  slot($name);
}
