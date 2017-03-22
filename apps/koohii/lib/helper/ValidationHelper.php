<?php
/**
 * Form Validation helpers.
 * 
 * 
 * 
 * @author     Fabrice Denis
 */

/**
 * Output all validation errors.
 * 
 * @return 
 */
function form_errors()
{
  $request = sfContext::getInstance()->getRequest();

  $s = '';
  if($request->hasErrors())
  {
    foreach($request->getErrors() as $key => $message)
    {
      $s .= '<strong>'.esc_specialchars($message).'</strong><br />'."\n";
    }
    $s = content_tag('div', $s, array('class' => 'formerrormessage'));
  }
  return $s;
}
