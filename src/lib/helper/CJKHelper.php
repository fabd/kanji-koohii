<?php
/**
 * Create a parent span element with language attributes.
 *
 * EXAMPLES
 *   <span lang="ja">Japanese text</span>
 *   <span lang="zh-Hant">Chinese traditional</span>
 *   <span lang="zh-Hans">Chinese simplified</span>
 *
 * SOURCES
 *   https://www.w3.org/International/questions/qa-css-lang#which
 *
 * @param string $html       Content to wrap with the language tag, is not escaped!
 * @param array  $classNames Optional class names to add to the SPAN
 */
function cjk_lang_ja($html, $classNames = [])
{
  return '<span '.cjk_lang_attrs($classNames).'>'.$html.'</span>';
}

function cjk_lang_attrs($classNames)
{
  $lang = CJ_HANZI ? 'zh-Hant' : 'ja';
  array_push($classNames, CJ_HANZI ? 'cj-t' : 'cj-k');

  return 'class="'.implode(' ', $classNames).'" lang="'.$lang.'" ';
}
