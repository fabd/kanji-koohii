<?php
/**
 * Change Password validation.
 * 
 */

return array
(
  'fields' => array
  (
    'oldpassword' => array
    (
      'required'       => array
      (
        'msg'       => 'Please enter your current password.'
      ),
      'StringValidator'   => array
      (
        'min'       => 5,
        'min_error'   => 'Password is too short (min 5 characters).',
        'max'       => 40,
        'max_error'   => 'Password is too long (max 40 characters).'
      ),
      'RegexValidator'    => array
      (
        'match'     => true,
        'pattern'     => '/^[\x20-\x7e]+$/',
        'match_error'   => 'Password: please use only ASCII printable characters.'
      )
    ),
    'newpassword' => array
    (
      'required'       => array
      (
        'msg'       => 'Please enter the new password.'
      ),
      'StringValidator'   => array
      (
        'min'       => 5,
        'min_error'   => 'Password is too short (min 5 characters).',
        'max'       => 40,
        'max_error'   => 'Password is too long (max 40 characters).'
      ),
      'RegexValidator'    => array
      (
        'match'     => true,
        'pattern'     => '/^[\x20-\x7e]+$/',
        'match_error'   => 'Password: please use only ASCII printable characters.'
      )
    ),
    'newpassword2' => array
    (
      'required'       => array
      (
        'msg'       => 'Please retype the new password.'
      ),
      'CompareValidator'  => array
      (
        'check'      => 'newpassword',
        'compare_error' => 'The passwords don\'t match.'
      )
    )
  )
);
