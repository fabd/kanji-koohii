<?php
/**
 * Register Account validation.
 * 
 */

return [
  'fields' => [
    // The restrictions must match those of PunBB since the forum account
    // is created with the same username and password.
    'username' => [
      'required'       => [
        'msg'       => 'Username is required.'
      ],
      'StringValidator'   => [
        'min'       => 5,
        'min_error'   => 'Username is too short (min 5 characters).',
        // Note: PunBB username max length is 25
        'max'       => 25,
        'max_error'   => 'Username is too long (max 25 characters).'
      ],
      'CallbackValidator' => [
        'callback'    => ['rtkValidators', 'validateUsername'],
        'invalid_error' => 'Username: please use only letters, digits and underscores. Start with a letter, end with letter or digit, no double underscores.'
      ]
    ],
    'email' => [
      'required'       => [
        'msg'       => 'Email is required.'
      ],
      'EmailValidator'   => [
        'strict'     => true,
        'email_error'   => 'Email is not valid.'
      ],
      'StringValidator'   => [
        'min'       => 7,
        'min_error'   => 'Email is too short (min 7 characters).',
        'max'       => 50,
        'max_error'   => 'Email is too long (max 50 characters).'
      ]
    ],
    'password' => [
      'required'       => [
        'msg'       => 'Please enter password.'
      ],
      'StringValidator'   => [
        'min'       => 5,
        'min_error'   => 'Password is too short (min 5 characters).',
        'max'       => 256,
        'max_error'   => 'Password is too long (max 256 characters).'
      ],
      'RegexValidator'    => [
        'match'     => true,
        'pattern'     => '/^[\x20-\x7e]+$/',
        'match_error'   => 'Password: please use only ASCII printable characters.'
      ]
    ],
    'password2' => [
      'required'       => [
        'msg'       => 'Please retype the password.'
      ],
      'CompareValidator'  => [
        'check'      => 'password',
        'compare_error' => 'The passwords don\'t match.'
      ]
    ],
    'location' => [
      // 30 characters is the PunBB forum limit
      'StringValidator'   => [
        'max'       => 30,
        'max_error'   => 'Location is too long (max 30 characters).'
      ],
      'CallbackValidator' => [
        'callback'        => ['rtkValidators', 'validateUserLocation'],
        'invalid_error'   => 'Location: invalid characters'
      ]
    ]
  ]
];
