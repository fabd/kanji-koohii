<?php
/**
 * Form Validation helpers.
 *
 * @author     Fabrice Denis
 */

/**
 * Output all validation errors.
 */
function form_errors(): string
{
  /** @var coreRequest $request */
  $request = sfContext::getInstance()->getRequest();

  $s = '';
  if ($request->hasErrors()) {
    foreach ($request->getErrors() as $message) {
      $s .= '<strong>'.esc_specialchars($message).'</strong><br />'."\n";
    }
    $s = content_tag('div', $s, ['class' => 'formerrormessage']);
  }

  return $s;
}
