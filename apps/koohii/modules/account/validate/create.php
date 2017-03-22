<?php
/**
 * Register Account validation.
 * 
 */

return array
(
  'fields' => array
  (
    // The restrictions must match those of PunBB since the forum account
    // is created with the same username and password.
    'username' => array
    (
      'required'       => array
      (
        'msg'       => 'Username is required.'
      ),
      'StringValidator'   => array
      (
        'min'       => 5,
        'min_error'   => 'Username is too short (min 5 characters).',
        // Note: PunBB username max length is 25
        'max'       => 25,
        'max_error'   => 'Username is too long (max 25 characters).'
      ),
      'CallbackValidator' => array
      (
        'callback'    => array('rtkValidators', 'validateUsername'),
        'invalid_error' => 'Username: please use only letters, digits and underscores. Start with a letter, end with letter or digit, no double underscores.'
      )
    ),
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
    'password' => array
    (
      'required'       => array
      (
        'msg'       => 'Please enter password.'
      ),
      'StringValidator'   => array
      (
        'min'       => 5,
        'min_error'   => 'Password is too short (min 5 characters).',
        'max'       => 256,
        'max_error'   => 'Password is too long (max 256 characters).'
      ),
      'RegexValidator'    => array
      (
        'match'     => true,
        'pattern'     => '/^[\x20-\x7e]+$/',
        'match_error'   => 'Password: please use only <a target="_blank" href="http://en.wikipedia.org/wiki/ASCII#ASCII_printable_characters">ASCII printable characters</a>.'
      )
    ),
    'password2' => array
    (
      'required'       => array
      (
        'msg'       => 'Please retype the password.'
      ),
      'CompareValidator'  => array
      (
        'check'      => 'password',
        'compare_error' => 'The passwords don\'t match.'
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
        'match'     => true,
        'pattern'     => '/^([a-zA-Z0-9])+([a-zA-Z0-9 \'-])*$/',
        'match_error'   => 'Location: only letters and digits, spaces, single quotes or dashes.'
      )
    )
  )
);
