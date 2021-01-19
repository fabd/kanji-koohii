<?php

/**
 * Delete Account validation.
 * 
 */

return [
  'fields' => [
    'email' => [
      'required' => [
        'msg' => 'Email is required.'
      ],
      'EmailValidator' => [
        'strict' => true,
        'email_error' => 'Email is not valid.'
      ]
    ],
    'password' => [
      'required' => [
        'msg' => 'Please enter password.'
      ],
    ]
  ]
];
