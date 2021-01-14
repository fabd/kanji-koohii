<?php
/**
 * Edit Account validation.
 * 
 */

return [
  'fields' => [
    'email' => [
      'required'       => [
        'msg'       => 'Email is required.'
      ]
      ,
      'EmailValidator'   => [
        'strict'     => true,
        'email_error'   => 'Email is not valid.'
      ]
      ,
      'StringValidator'   => [
        'min'       => 7,
        'min_error'   => 'Email is too short (min 7 characters).',
        'max'       => 50,
        'max_error'   => 'Email is too long (max 50 characters).'
      ]
    ],

    'location' => [
      // fixme (obsolete) : 30 character limit was linked to old PunBB account creation
      'StringValidator'   => [
        'max'             => 30,
        'max_error'       => 'Location is too long (max 30 characters).'
      ]
      ,
      'CallbackValidator' => [
        'callback'        => ['rtkValidators', 'validateUserLocation'],
        'invalid_error'   => 'Location: invalid characters'
      ]
    ]
  ]
];
