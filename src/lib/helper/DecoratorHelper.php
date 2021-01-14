<?php
/**
 * DecoratorHelper
 * 
 *   Simple decorator template mechanism to wrap a section or entire view with another
 *   template.
 *
 *   The calling template will be buffered between the decorate_start() and
 *   end() methods. Therefore, the context (template variables) is unchanged.
 *
 *   Once decorate_end() is called, the decorator is rendered, and the buffered
 *   content is output with $decorate_content.
 *
 *
 * FooDecorator.php (/apps/<appname>/templates/FooDecorator.php):
 *
 *   <div class="foo-decorator">
 *     ... echo $decorator_content; ...
 *   </div>
 *
 *
 * Calling view:
 *
 *   ... decorate_start('Foo', array('myvar' => true)) ...
 *   ... some html ...
 *   ... decorate_end()
 *
 */

// start buffering section of the view that will be surrounded by the decorator
function decorate_start($name, $vars = [])
{
  sfConfig::set('view.decorator.name', $name);
  sfConfig::set('view.decorator.vars', $vars);

  ob_start();
  ob_implicit_flush(0);
}

function decorate_end()
{
  $decorator_content = ob_get_clean();

  $template = sfConfig::get('view.decorator.name').'Decorator.php';
  $vars = sfConfig::get('view.decorator.vars', []);

  // vars for the decorator
  extract($vars, EXTR_REFS);
  require(sfConfig::get('sf_app_template_dir').DIRECTORY_SEPARATOR.$template);
}

