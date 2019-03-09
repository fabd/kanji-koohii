<?php
/**
 * Sightreading page validation.
 * 
 */

return array
(
  'fields' => array
  (
    'jtextarea' => array
    (
      'required'       => array
      (
        'msg'       => 'Please enter some japanese text.'
      ),
      'StringValidator'   => array
      (
        'max'       => 20000,
        'max_error'   => 'Text is too long (max 20000 characters).'
      )
    )
  )
);
