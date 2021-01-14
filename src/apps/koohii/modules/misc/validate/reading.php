<?php
/**
 * Sightreading page validation.
 * 
 */

return [
  'fields' => [
    'jtextarea' => [
      'required'       => [
        'msg'       => 'Please enter some japanese text.'
      ],
      'StringValidator'   => [
        'max'       => 20000,
        'max_error'   => 'Text is too long (max 20000 characters).'
      ]
    ]
  ]
];
