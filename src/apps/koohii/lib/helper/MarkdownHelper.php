<?php
/**
 * Helpers to parse Markdown.
 *
 *   markdown_parsedown_extra($filename)      Load (.md) file contents and return parsed html
 */

/* old code - obsolete?
function markdown_parsedown_extra($filename)
{
  if (false === ($contents = file_get_contents($filename))) {
    $contents = 'Woops. Unable to read filename';
  }

  $parsedown = new ParsedownExtra();

  // do not escape custom html in the markdown text
  $parsedown->setMarkupEscaped(false);

  // prevents automatic linking of URLs
  // $Parsedown->setUrlsLinked(false);

  $html = $parsedown->text($contents);

  return $html;
}
*/

/**
 * Begin capturing text in Markdown format (with ParsedownExtra extensions).
 */
function markdown_begin()
{
  ob_start();
}

/**
 * End capturing Markdown text.
 *
 * Returns the generated html text, in case you'd want to do additional replacements before echoing to the template.
 *
 * @return string The generated html
 */
function markdown_end()
{
  $markdown = ob_get_clean();

  $parsedown = new ParsedownExtra();

  // do not escape custom html in the markdown text
  $parsedown->setMarkupEscaped(false);

  $html = $parsedown->text($markdown);

  return $html;
}
