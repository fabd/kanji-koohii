<?php
/**
 * Login validation.
 * 
 */

return array
(
  'fields' => array
  (
    'username' => array
    (
      'required'       => array
      (
        'msg'       => 'Please enter username.'
      ),
      'StringValidator'   => array
      (
        'min'       => 5,
        'min_error'   => 'Username is too short (min 5 characters).',
        // Note: PunBB username max length is 25
        'max'       => 25,
        'max_error'   => 'Username is too long (max 25 characters).'
      )
      /*
        Don't validate username at login because the username character
        restrictions have changed over time. Validate at creation.
      */
    ),
    'password' => array
    (
      'required'       => array
      (
        'msg'       => 'Please enter password.'
      ),
      'RegexValidator'    => array
      (
        'match'     => true,
        'pattern'     => '/^[\x20-\x7e]+$/',
        'match_error'   => 'Password: please use only <a target="_blank" href="http://en.wikipedia.org/wiki/ASCII#ASCII_printable_characters">ASCII printable characters</a>.'
      )
    )
  )
);
