<?php
/**
 * Feedback Form validation.
 * 
 */

return array
(
  'fields' => array
  (
    'name' => array
    (
      'required'       => array
      (
        'msg'       => 'Please enter your name or a nickname.'
      ),
      'StringValidator'   => array
      (
        'max'       => 100,
        'max_error'   => 'Name is too long (max 100 characters).'
      ),
    ),
    'email' => array
    (
      'required'       => array
      (
        'msg'       => 'Please enter a valid email address so that I can reply to you.'
      ),
      'EmailValidator'   => array
      (
        'strict'     => true,
        'email_error'   => 'Email is not valid.'
      ),
      'StringValidator'   => array
      (
        'max'       => 100,
        'max_error'   => 'Email is too long (max 100 characters).'
      )
    ),
    'message' => array
    (
      'required'       => array
      (
        'msg'       => 'Please enter your message.'
      ),
      'CallbackValidator' => array
      (
        'callback'    => array('BaseValidators', 'validateNoHtmlTags'),
        'invalid_error' => 'No html formatting'
      )
    ),
  )
);
