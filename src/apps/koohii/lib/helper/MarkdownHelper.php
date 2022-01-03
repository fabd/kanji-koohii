<?php
/**
 * Helpers to parse Markdown.
 *
 *   markdown_parsedown_extra($filename)      Load (.md) file contents and return parsed html
 * 
 */

function markdown_parsedown_extra($filename)
{
  if (false === ($contents = file_get_contents($filename))) {
    $contents = "Woops. Unable to read filename";
  }

  $Parsedown = new ParsedownExtra();

  // do not escape custom html in the markdown text
  $Parsedown->setMarkupEscaped(false);

  // prevents automatic linking of URLs
  // $Parsedown->setUrlsLinked(false);

  $html = $Parsedown->text($contents);

  return $html;
}
