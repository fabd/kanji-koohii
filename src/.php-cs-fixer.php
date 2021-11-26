<?php

return (new PhpCsFixer\Config())
  ->setRules([
    // rulesets
    '@PSR2' => true,
    '@PhpCsFixer' => true,
    '@PHP74Migration' => true,

    'array_syntax' => ['syntax' => 'short'],

    // oldschool Symfony style, verbose but more readable
    'braces' => [
      'allow_single_line_closure' => true,
      'position_after_anonymous_constructs' => 'next',
      'position_after_control_structures' => 'next',
    ],

    // always use heredoc
    'heredoc_to_nowdoc' => false,

    // *don't* prematurely remove else's
    'no_useless_else' => false,

    // *don't* reorder public/private/etc: too much diffs in legacy code
    'ordered_class_elements' => false,

    // prefer `string|null`
    'phpdoc_types_order' => [
      'null_adjustment' => 'always_last',
    ],

    // *don't* prematurely rewrite my code
    'return_assignment' => false,

    // for ($i = 0;; ++$i)  ==>  for ($i = 0; ; ++$i)
    'space_after_semicolon' => ['remove_in_empty_for_expressions' => false],

    // *ignore* yoda style (occasional use)
    'yoda_style' => false,
  ])

    // use oldschool Symfony 2-spaces-per-tabs
  ->setIndent('  ')

    // linux line endings
  ->setLineEnding("\n")
;
