<?php

use PhpCsFixer\Config;

return (new Config())
  ->setRules([
    // rulesets
    '@PSR2' => true,
    '@PhpCsFixer' => true,

    // koohii server currently runs php 8.2.30
    '@PHP8x2Migration' => true,

    'array_syntax' => ['syntax' => 'short'],

    'binary_operator_spaces' => [
      'default' => 'align_single_space_minimal',
    ],

    'blank_line_after_opening_tag' => false,

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

    // $i++ not ++$i
    'increment_style' => ['style' => 'post'],

    // allow cleaner one-line conditionals in php templates :
    //   <!php if (expr): !> ... <!php else: !> ... <!php endif; !>
    'no_alternative_syntax' => [
      'fix_non_monolithic_code' => false,
    ],

    // *don't* prematurely remove else's
    'no_useless_else' => false,

    // *don't* reorder public/private/etc: too much diffs in legacy code
    'ordered_class_elements' => false,

    // IMPORTANT - prevents breaking single line cast for PHPStan eg. /** @var <type> $foo */
    'phpdoc_to_comment' => false,

    // prefer `string|null`
    'phpdoc_types_order' => [
      'null_adjustment' => 'always_last',
    ],

    // *don't* prematurely rewrite my code
    'return_assignment' => false,

    // don't reformat my top of class comment block if there is a single line of text
    'single_line_comment_style' => ['comment_types' => ['hash']],

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
