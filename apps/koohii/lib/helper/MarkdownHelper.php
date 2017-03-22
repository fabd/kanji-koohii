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

  $Parsedown->setMarkupEscaped(false);

  // prevents automatic linking of URLs
  // ->setUrlsLinked(false) 

  $html = $Parsedown->text($contents);

  return $html;
}
