<?php
/**
 * CJKHelper, for RevTK and RevTH.
 * 
 * @author   Fabrice Denis
 */

/**
 * Create a parent span element with language attributes.
 *
 * TODO rename to cjk_lang_attr() because it is now RTH/RTK sensitive.
 *
 * Examples:
 *   <span lang="ja" xml:lang="ja">Japanese text</span>
 *   <span lang="zh-Hant" xml:lang="zh-Hant">Chinese traditional</span>
 *   <span lang="zh-Hans" xml:lang="zh-Hans">Chinese simplified</span>
 * 
 * @param  string   $html   Content to wrap with the language tag, is not escaped!
 * @param  array    $classNames   Optional class names to add to the SPAN
 */
function cjk_lang_ja($html, $classNames = [])
{
  return '<span '.cjk_lang_attrs($classNames).'>'.$html.'</span>';
}

function cjk_lang_attrs($classNames)
{
  $lang = CJ_HANZI ? 'zh-Hant' : 'ja';
  array_push($classNames, CJ_HANZI ? 'cj-t' : 'cj-k');
  return 'class="'.implode(' ', $classNames).'" lang="'.$lang.'" xml:lang="'.$lang.'"';
}
