<?php
/**
 * summary validation.
 * 
 * Checks required post variables sent by the uiFlashcardReview component
 * at the end of a review session.
 * 
 */

return [
  'fields' => [
    'ts_start' => [
      'required'        => [
        'msg'           => 'Error'
      ],
      'CallbackValidator' => [
        'callback'        => ['BaseValidators', 'validateInteger'],
        'invalid_error'   => 'Validation failed'
      ]
    ],

    'fc_pass' => [
      'required'        => [
        'msg'           => 'Error'
      ],
      'CallbackValidator' => [
        'callback'        => ['BaseValidators', 'validateInteger'],
        'invalid_error'   => 'Validation failed'
      ]
    ],

    'fc_fail' => [
      'required'        => [
        'msg'           => 'Error'
      ],
      'CallbackValidator' => [
        'callback'        => ['BaseValidators', 'validateInteger'],
        'invalid_error'   => 'Validation failed'
      ]
    ],

    // free mode review flag
    'fc_free' => [
      'required'        => [
        'msg'           => 'Error'
      ],
      'CallbackValidator' => [
        'callback'        => ['BaseValidators', 'validateInteger'],
        'invalid_error'   => 'Validation failed'
      ]
    ]

  ]
];
