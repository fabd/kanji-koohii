<?php
/**
 * Helpers to create links to application resources.
 * 
 * 
 * @author  Fabrice Denis
 */

/**
 * Creates a link to a member's profile page.
 *
 * Uses rel="nofollow" to disable indexation of all the profile pages.
 * 
 * @param  string    $username   Username
 * @param  array     $options    Optional attributes for the link, see link_to()
 */
function link_to_member($username, $options = [])
{
  $internal_uri = '@profile?username='.$username;
  $options = array_merge($options, ['rel' => 'nofollow']);
  return link_to($username, $internal_uri, $options);
}

/**
 * This helper creates a link to the Study area page for a given character.
 * 
 * @param string  $sKeyword   The link text
 * @param mixed   $sKanjiId   Kanji as a utf8 character, frame number or keyword
 */
function link_to_keyword($sKeyword, $sKanjiId = '', $options = [])
{
  if ($sKanjiId === '') {
    $sKanjiId = $sKeyword;
  }

  // for the review page front end
  $classNames = isset($options['class']) ? $options['class'] : '';
  $options['class'] = trim(implode(' ', [$classNames, 'JsKeywordLink']));

  return link_to($sKeyword, '@study_edit?id='.$sKanjiId, $options);
}

/**
 * Helper which creates a link to the community forum.
 * 
 * Arguments meanings are similar to link_to() but the url should be
 * a plain url, relative to the forum domain. The forum base url is
 * defined in settings.php
 *
 * @param string  $text     Link label, cf. link_to()
 * @param string  $rel_url  Url relative to base forum url, must begin with "/"
 *
 * @return string   Link tag
 */
function link_to_forum($text, $rel_url, $options = [])
{
  $url = sfConfig::get('app_forum_url') . $rel_url;
  return link_to($text, $url, $options);
}

