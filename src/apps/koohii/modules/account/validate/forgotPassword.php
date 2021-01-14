<?php
/**
 * Forgot Password validation.
 * 
 */

return [
  'fields' => [
    'email_address' => [
      'required'         => [
        'msg'           => 'Email address is required.'
      ],
      'EmailValidator'  => [
        'strict'        => true,
        'email_error'   => 'Email is not valid.'
      ]
      
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
    ]
  ]
];
