<?php
/**
 * summary validation.
 * 
 * Checks required post variables sent by the uiFlashcardReview component
 * at the end of a review session.
 * 
 */

return array
(
  'fields' => array
  (
    'ts_start' => array
    (
      'required'        => array
      (
        'msg'           => 'Error'
      ),
      'CallbackValidator' => array
      (
        'callback'        => array('BaseValidators', 'validateInteger'),
        'invalid_error'   => 'Validation failed'
      )
    ),

    'fc_pass' => array
    (
      'required'        => array
      (
        'msg'           => 'Error'
      ),
      'CallbackValidator' => array
      (
        'callback'        => array('BaseValidators', 'validateInteger'),
        'invalid_error'   => 'Validation failed'
      )
    ),

    'fc_fail' => array
    (
      'required'        => array
      (
        'msg'           => 'Error'
      ),
      'CallbackValidator' => array
      (
        'callback'        => array('BaseValidators', 'validateInteger'),
        'invalid_error'   => 'Validation failed'
      )
    ),

    // free mode review flag
    'fc_free' => array
    (
      'required'        => array
      (
        'msg'           => 'Error'
      ),
      'CallbackValidator' => array
      (
        'callback'        => array('BaseValidators', 'validateInteger'),
        'invalid_error'   => 'Validation failed'
      )
    )

  )
);
