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

    // *don't* add backslashes everywhere
    'global_namespace_import' => [
      'import_classes' => true,
      'import_constants' => true,
      'import_functions' => true,
    ],

    // always use heredoc
    'heredoc_to_nowdoc' => false,

    // heredoc should always start at column 1, easy to see, more space for html
    'heredoc_indentation' => false,

    // allow cleaner one-line conditionals in php templates :
    //   <!php if (expr): !> ... <!php else: !> ... <!php endif; !>
    'no_alternative_syntax' => [
      'fix_non_monolithic_code' => false,
    ],

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

    'echo_tag_syntax' => ['format' => 'short', 'shorten_simple_statements_only' => true],

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
