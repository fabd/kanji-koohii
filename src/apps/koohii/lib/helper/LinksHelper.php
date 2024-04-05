<?php
/*
 * Helpers to create links to application resources.
 *
 */

/**
 * Creates a link to a member's profile page.
 *
 * Uses rel="nofollow" to disable indexation of all the profile pages.
 *
 * @param string $username Username
 * @param array  $options  Optional attributes for the link, see link_to()
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
 * @param string $sKeyword The link text
 * @param mixed  $sKanjiId Kanji as a utf8 character, frame number or keyword
 * @param mixed  $options
 */
function link_to_keyword($sKeyword, $sKanjiId = '', $options = [])
{
  if ($sKanjiId === '') {
    $sKanjiId = $sKeyword;
  }

  // for the review page front end
  $classNames = $options['class'] ?? '';
  $options['class'] = trim(implode(' ', [$classNames, 'JsLink']));

  return link_to($sKeyword, '@study_edit?id='.$sKanjiId, $options);
}

function link_to_rk1_supplement()
{
  $URL = 'https://web.archive.org/web/20200227181804/http://nirc.nanzan-u.ac.jp/en/files/2012/12/RK1-Supplement.pdf';

  return link_to('RTK Supplement', $URL, [
    'target' => 'blank',
    'class' => 'link-pdf',
  ]);
}
