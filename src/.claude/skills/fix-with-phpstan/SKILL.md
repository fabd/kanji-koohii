---
name: fix-with-phpstan
description: Runs PHPStan on a file, and fix the errors.
disable-model-invocation: true
---

Run PHPStan on $ARGUMENTS and fix the errors.

When you apply changes to the PHP code:

- use PHP 8.1+ syntax with constructor property promotion
- Strict comparisons only (===, !==)
- Braces required for all control structures
