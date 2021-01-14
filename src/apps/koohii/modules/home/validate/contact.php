<?php
/**
 * Feedback Form validation.
 * 
 */

return [
  'fields' => [
    'name' => [
      'required'       => [
        'msg'       => 'Please enter your name or a nickname.'
      ],
      'StringValidator'   => [
        'max'       => 100,
        'max_error'   => 'Name is too long (max 100 characters).'
      ],
    ],
    'email' => [
      'required'       => [
        'msg'       => 'Please enter a valid email address so that I can reply to you.'
      ],
      'EmailValidator'   => [
        'strict'     => true,
        'email_error'   => 'Email is not valid.'
      ],
      'StringValidator'   => [
        'max'       => 100,
        'max_error'   => 'Email is too long (max 100 characters).'
      ]
    ],
    'message' => [
      'required'       => [
        'msg'       => 'Please enter your message.'
      ],
      'CallbackValidator' => [
        'callback'    => ['BaseValidators', 'validateNoHtmlTags'],
        'invalid_error' => 'No html formatting'
      ]
    ],
  ]
];
