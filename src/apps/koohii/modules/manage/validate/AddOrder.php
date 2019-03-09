<?php
/**
 * AddOrder validation.
 * 
 */

return array
(
  'fields' => array
  (
    'txtSelection' => array
    (
      'required'        => array
      (
        'msg'           => 'Selection text can not be left blank'
      ),
      
      'RegexValidator'  => array
      (
        'match'         => true,
        'pattern'       => "/^\+?[0-9]+$/",
        'match_error'   => 'Please enter a valid RTK index (eg: "512") or range of cards (eg. "+10")'
      )
    )
  )
);
