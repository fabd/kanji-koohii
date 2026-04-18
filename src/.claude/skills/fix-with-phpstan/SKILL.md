---
name: fix-with-phpstan
description: Runs PHPStan on a file, and fix the errors.
disable-model-invocation: true
---

Run PHPStan on $ARGUMENTS and fix the errors.

## When making changes to a function

- add types to the function signature
- add the return type to the function signature if possible, eg. `function foo(): string {}`, EXCEPT for `void`
- when you see a parameter of type mixed, try to infer a more narrow type and update it. Look at the caller sites to see if there is an obvious type which is more narrow than `mixed`. If there are several options, ask the user which to use.

## Fixing "Access to an undefined property" in a Symfony action

Symfony sets template variables by assigning them as properties on $this in the action.

This will trigger PHPStan error "Access to an undefined property".

The simplest fix is to declare those properties in the class PHPDoc like so:

```
/**
 *
 * @property string $templateVar
 */
class aboutActions extends sfActions
{
  // action sets a template variable dynamically
  $this->templateVar = "lol cat";
```
