---
name: fix-with-phpstan
description: Fix PHPStan static analysis errors in PHP files. Use this skill whenever the user asks to "fix PHPStan errors", "run PHPStan on", or "clean up types in" one or more PHP files or a module directory.
disable-model-invocation: true
argument-hint: "[filename.php or path/to/module/]"
---

Fix PHPStan errors in the file(s) or directory specified in $ARGUMENTS.

## When making changes to a function

- Add types to parameters where you can infer them confidently from call sites
- Add the return type to the signature where possible, e.g. `function foo(): string {}`
- When a parameter is typed `mixed`, look at call sites for a narrower type. If there are multiple plausible options, ask the user.

## Fixing "Access to an undefined property" in a Symfony action

Symfony actions set template variables as `$this->varName` inside action methods,
which triggers this PHPStan error. The correct fix is a class PHPDoc declaration:

```php
/**
 * @property string $templateVar
 */
class aboutActions extends sfActions
{
    public function executeIndex(): void
    {
        $this->templateVar = "value";
    }
}
```
