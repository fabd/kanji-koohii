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
function cjk_lang_ja($html, $classNames = array())
{
  return '<span '.cjk_lang_attrs($classNames).'>'.$html.'</span>';
}

function cjk_lang_attrs($classNames)
{
  $lang = CJ_HANZI ? 'zh-Hant' : 'ja';
  array_push($classNames, CJ_HANZI ? 'cj-t' : 'cj-k');
  return 'class="'.implode(' ', $classNames).'" lang="'.$lang.'" xml:lang="'.$lang.'"';
}

/**
 * Create a SPAN element containing the reading for a character:
 *
 * - For Japanese, ON reading is expected, and show in katakana
 * - For Chinese,      pinyin is expected, and show in diacritic format
 * 
 * @param  mixed   $onyomi    ON reading in katakana, or Pinyin reading as tone+number
 */
function format_readings($onyomi)
{
  if (empty($onyomi))
  {
    return '';
  }

  if (CJ_HANZI)
  {
    sfProjectConfiguration::getActive()->loadHelpers(array('Tag', 'Pinyin'));

    // hanzi site has Pinyin readings
    $pinyin = explode(',', $onyomi);
    $pinyin = array_slice($pinyin, 0, 2);
    $tones  = array();
    foreach ($pinyin as $tone) {
      array_push($tones, pinyin_ntod($tone));
    }

    $html = content_tag('span', implode('<br/>', $tones), array('title' => implode(', ', $pinyin)));
  }
  else
  {
    $html = '<span title="ON reading" style="font-size:120%">'.cjk_lang_ja($onyomi).'</span>';
  }

  return $html;
}

