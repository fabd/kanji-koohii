<?php
/**
 * Change Password validation.
 * 
 */

return [
  'fields' => [
    'oldpassword' => [
      'required'       => [
        'msg'       => 'Please enter your current password.'
      ],
      'StringValidator'   => [
        'min'       => 5,
        'min_error'   => 'Password is too short (min 5 characters).',
        'max'       => 40,
        'max_error'   => 'Password is too long (max 40 characters).'
      ],
      'RegexValidator'    => [
        'match'     => true,
        'pattern'     => '/^[\x20-\x7e]+$/',
        'match_error'   => 'Password: please use only ASCII printable characters.'
      ]
    ],
    'newpassword' => [
      'required'       => [
        'msg'       => 'Please enter the new password.'
      ],
      'StringValidator'   => [
        'min'       => 5,
        'min_error'   => 'Password is too short (min 5 characters).',
        'max'       => 40,
        'max_error'   => 'Password is too long (max 40 characters).'
      ],
      'RegexValidator'    => [
        'match'     => true,
        'pattern'     => '/^[\x20-\x7e]+$/',
        'match_error'   => 'Password: please use only ASCII printable characters.'
      ]
    ],
    'newpassword2' => [
      'required'       => [
        'msg'       => 'Please retype the new password.'
      ],
      'CompareValidator'  => [
        'check'      => 'newpassword',
        'compare_error' => 'The passwords don\'t match.'
      ]
    ]
  ]
];
