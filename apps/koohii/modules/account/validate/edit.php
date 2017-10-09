<?php
/**
 * Edit Account validation.
 * 
 */

return array
(
  'fields' => array
  (
    'email' => array
    (
      'required'       => array
      (
        'msg'       => 'Email is required.'
      ),
      'EmailValidator'   => array
      (
        'strict'     => true,
        'email_error'   => 'Email is not valid.'
      ),
      'StringValidator'   => array
      (
        'min'       => 7,
        'min_error'   => 'Email is too short (min 7 characters).',
        'max'       => 50,
        'max_error'   => 'Email is too long (max 50 characters).'
      )
    ),
    'location' => array
    (
      // 30 characters is the PunBB forum limit
      'StringValidator'   => array
      (
        'max'       => 30,
        'max_error'   => 'Location is too long (max 30 characters).'
      ),
      'RegexValidator'   => array
      (
        'match'       => true,
        'pattern'     => '/^[\x20-\x7e]+$/',
        'match_error' => 'Location: please use only ASCII printable characters.'
        // 'match'     => true,
        // 'pattern'     => '/^([a-zA-Z0-9])+([a-zA-Z0-9 \'-])*$/',
        // 'match_error'   => 'Location: only letters and digits, spaces, single quotes or dashes.'
      )
    )
  )
);
