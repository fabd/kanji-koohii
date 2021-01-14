<?php
/**
 * Login validation.
 * 
 */

return [
  'fields' => [
    'username' => [
      'required'       => [
        'msg'       => 'Please enter username.'
      ],
      'StringValidator'   => [
        'min'       => 5,
        'min_error'   => 'Username is too short (min 5 characters).',
        // Note: PunBB username max length is 25
        'max'       => 25,
        'max_error'   => 'Username is too long (max 25 characters).'
      ]
      /*
        Don't validate username at login because the username character
        restrictions have changed over time. Validate at creation.
      */
    ],
    'password' => [
      'required'       => [
        'msg'       => 'Please enter password.'
      ],
      'RegexValidator'    => [
        'match'     => true,
        'pattern'     => '/^[\x20-\x7e]+$/',
        'match_error'   => 'Password: please use only ASCII printable characters.'
      ]
    ]
  ]
];
