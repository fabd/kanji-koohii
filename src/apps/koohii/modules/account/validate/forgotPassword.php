<?php
/**
 * Forgot Password validation.
 * 
 */

return array
(
  'fields' => array
  (
    'email_address' => array
    (
      'required'         => array
      (
        'msg'           => 'Email address is required.'
      ),
      'EmailValidator'  => array
      (
        'strict'        => true,
        'email_error'   => 'Email is not valid.'
      )
      
      /*
      'StringValidator'   => array
      (
        'min'       => 5,
        'min_error'   => 'Username is too short (min 5 characters).',
        // Note: PunBB username max length is 25
        'max'       => 25,
        'max_error'   => 'Username is too long (max 25 characters).'
      )
      */
    )
  )
);
