<?php
/**
 * FrontEndHelper : helpers for outputting Javascript from php.
 *
 * @param mixed $value
 */

// echoes a javascript string escaped, and "quoted"
function js_string_quoted($value)
{
  $s = (string) $value;
  $s = escape_javascript($s);

  return "\"{$s}\"";
}
