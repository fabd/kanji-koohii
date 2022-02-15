<?php
/**
 * Helpers to output confirmation and error messages.
 * 
 * @todo  Refactor into something more generic:
 *        - better css naming
 *        - better structure (use P or LI for error messages)
 * 
 * @package  Helpers
 * @author   Fabrice Denis
 */

/**
 * Return an error message box html code, with any error messages that
 * were set in the Request object.
 * 
 * @return string  Html code to echo
 */
function form_errors()
{
  $request = sfContext::getInstance()->getRequest();
  $s = '';
  if ($request->hasErrors())
  {
    $s = implode("<br/>\n", array_values($request->getErrors()));
    $s = content_tag('p', $s, ['class' => 'ico ico-error']);
    $s = content_tag('div', $s, ['class' => 'form-global-message']);
  }
  return $s;
}


/**
 * Return a confirmation message box html code, if any confirmation messages
 * were set in the Request object.
 * 
 * @return string  Html code to echo
 */
function form_confirmations()
{
  $request = sfContext::getInstance()->getRequest();
  $s = '';
  if ($request->hasConfirmations())
  {
    $s = implode("<br/>\n", array_values($request->getConfirmations()));
    $s = content_tag('p', $s);
    $s = content_tag('div', $s, ['class' => 'messagebox msgbox-success']);
    $s .= '<div class="clear-both"></div>';
  }
  return $s;
}
