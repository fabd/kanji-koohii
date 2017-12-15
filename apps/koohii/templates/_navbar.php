<?php 
function get_page_id() {
  $_request = sfContext::getInstance()->getRequest();
  return $_request->getParameter('module').'-'; //.$_request->getParameter('action');
}

function nav_active($nav_id) {
  $_page_id = get_page_id();
  // use strpos() so we can match all actions in a module, eg. 'module-'
  return (strpos($nav_id, $_page_id) === 0);
}

function nav_item($nav_id, $text, $internal_uri, $options = array(), $dropdown = false)
{
  $li_class = array();

  if (array_key_exists('_class', $options)) {
    $li_class[] = $options['_class'];
    unset($options['_class']);
  }

  if (nav_active($nav_id)) {
    $li_class[] = 'active';
  }

  if (false !== $dropdown) {
    $li_class[] = 'JsHasDropdown';

    $script = (CORE_ENVIRONMENT === 'prod') ? '' : sfContext::getInstance()->getRequest()->getScriptName();
    $dropdown = str_replace('@', $script, $dropdown);
  }

  if (false !== $dropdown) {
    $text .= ' <span class="arrow"></span>';
  }

  $link = link_to($text, $internal_uri, $options);

  $li_class = $li_class ? ' class="'.implode(' ', $li_class).'"' : '';

  return "<li${li_class}>\n  ${link}\n${dropdown}\n</li>\n";
}

?>
<div id="k-nav">
  <!-- mobile -->
  <nav id="k-nav_m">
    <div id="k-nav_m_bar">
      <a class="k-nav_btn k-nav_m_btn_main JsNavToggle" id="k-slide-nav-btn">
        <i class="nav-h fa fa-bars"></i>
      </a>
      <?php echo link_to('&nbsp;', '@homepage', array('class' => 'k-nav_brand')) ?>
    </div>
  </nav>

  <!-- desktop -->
  <nav id="k-nav_d">
    <div class="container">
<?php

    echo link_to('&nbsp;', '@homepage', array('class' => 'k-nav_brand'));

if (!$sf_user->isAuthenticated()) {
  // echo nav_item('about-learnmore', 'Learn More', 'about/learnmore');
  // echo nav_item('the-forum', '<strong>FORUM</strong>', 'http://forum.koohii.com');
  // echo nav_item('account-create', 'SIGN UP', 'account/create', array('_class' => 'sign-up align-right'));
      
  echo "<ul class=\"k-nav_right\">\n";

  echo nav_item('-', 'Sign In', 'account/index', array('_class' => 'align-right signed-in'));

  echo "</ul>\n";

} else {

  echo "<ul>\n";

  echo nav_item('study-index', 'Study', 'study/index', array(), <<<DOM
<ul class="k-nav_dropdown">
  <li><a href="@/study">Browse</a></li>
  <li><a href="@/study/failedlist">Restudy List</a></li>
  <li><a href="@/study/mystories" class="active">My Stories</a></li>
  <li><a href="@/sightreading">Kanji Recognition</a></li>
</ul>
DOM
  );

  echo nav_item('review-index', 'Review', '@overview', array(), <<<DOM
<ul class="k-nav_dropdown">
  <li><a href="@/main">Spaced Repetition</a></li>
  <li><a href="@/review/custom">Kanji Review</a></li>
  <li><a href="@/review/vocab">Vocab Shuffle</a></li>
  <li><a href="@/members">Who's Reviewing?</a></li>
</ul>
DOM
  );

  echo nav_item('manage-', 'Flashcards', '@manage', array(), <<<DOM
<ul class="k-nav_dropdown">
  <li><a href="@/manage">Manage Flashcards</a></li>
  <li><a href="@/manage/flashcardlist">Flashcard List</a></li>
</ul>
DOM
  );

  echo nav_item('more-index', '<i class="fa fa-bars"></i>', '@homepage', array(), <<<DOM
<ul class="k-nav_dropdown">
  <li><a href="@/learnmore">Help</a></li>
  <li><a href="@/contact">Contact</a></li>
  <li><a href="@/about/support">Donate</a></li>
  <li><a href="http://forum.koohii.com">Koohii Forum</a></li>
</ul>
DOM
  );
  echo "</ul>\n";

  echo "<ul class=\"k-nav_right\">\n";

  echo nav_item('about-support', '<span class="fa fa-heart"></span>', 'about/support', array('_class' => 'donate'));

  echo nav_item('account-', $sf_user->getUsername(), 'account/index', array('_class' => 'align-right signed-in'), <<<DOM
<ul class="k-nav_dropdown">
  <li><a href="@/account">Account Settings</a></li>
  <li><a href="@/profile">Member Profile</a></li>
  <li><a href="@/logout">Log out</a></li>
</ul>
DOM
  );

  /*if (1 && $sf_user->isAdministrator() && (null !== ($backend_url = sfConfig::get('app_backend_url')))) {
      echo '<li style="background:#11364B;"><a href="'. $backend_url .'"><i class="fa fa-lock" style="color:#eee;"></i></a>';
    } */

  echo "</ul>\n";
} ?>
      <div class="clear"></div>
    </div><!-- /container -->
  </nav>
</div>
<?php
// create the mobile menu data for the Aside component (cf. layout.php)

function nav_m_t($label, $id, $icon, $children) {
  return array(
    'label'    => $label,
    'id'       => $id,
    'icon'     => $icon,
    'children' => $children
  );
}

function nav_m_i($label, $id, $url) {
  $label  = link_to($label, $url);
  return array(
    'label'    => $label,
    'id'       => $id
  );
}

$nav_items = array();

if (!$sf_user->isAuthenticated()) {

  $nav_items[] = nav_m_i('Register',  'a-a',  'account/create');
  $nav_items[] = nav_m_i('Sign In',   'a-b',  '@login');

} else {

  $nav_items[] = nav_m_t('Study', 'study', 'fa-book', array(
    nav_m_i('Index',      's-i',  'study/index'),
    nav_m_i('Restudy',    's-r',  'study/failedlist' ),
    nav_m_i('My Stories', 's-ms', 'study/mystories' )
  ));
    
  $nav_items[] = nav_m_t('Review', 'review', 'fa-signal', array(
    nav_m_i('SRS',   'r-a', '@overview'),
    nav_m_i('Kanji', 'r-b', 'review/custom'),
    nav_m_i('Vocab', 'r-c', 'review/vocab')
  ));

  $nav_items[] = nav_m_t('Flashcards', 'flashcards', 'fa-copy', array(
    nav_m_i('Manage', 's-r', 'manage/addcustom'),
    nav_m_i('List',   's-i', 'manage/flashcardlist')
  ));

  $nav_items[] = nav_m_t('Account', 'account', 'fa-user', array(
    nav_m_i('Settings',  'a-a',  'account/index'),
    nav_m_i('Profile',   'a-b',  'profile'),
    nav_m_i('Log out',   'a-c',  '@logout' )
  ));

  $nav_items[] = nav_m_t('More', 'more', 'fa-ellipsis-h', array(
    nav_m_i('Help',    'h-a',  '@learnmore'),
    nav_m_i('Contact', 'h-b',  '@contact'),
    nav_m_i('Donate',  'h-c',  'about/support' ),
    nav_m_i('Forum',   'h-d',  'http://forum.koohii.com' )
  ));
}

// default opened
$defaultOpen = 'study';

$pageToMenu = array(
  'review-' => 'review',
  'manage-' => 'flashcards',
  'account-' => 'account', 
   'profile-' => 'account',
  'about-' => 'more', 'home-' => 'more'
);

foreach ($pageToMenu as $pageId => $open) {
  if (nav_active($pageId)) {
    $defaultOpen = $open; break;
  }
}

slot('koohii.nav.data', (object) array(
  'opened' => $defaultOpen,
  'items'  => $nav_items
));
