<?php 
function get_page_id(): string
{
  static $pageId =  null;

  if (!$pageId) {
    $request = sfContext::getInstance()->getRequest();
    $pageId = $request->getParameter('module').'-'.$request->getParameter('action');
  }

  return $pageId;
}

function nav_active(string $nav_id)
{
  $pageId = get_page_id();
  // use strpos() so we can match all actions in a module, eg. 'module-'
  return (strpos($pageId, $nav_id) === 0);
}

function nav_item(string $nav_id, string $text, string $internal_uri, $options = [], $dropdown = false)
{
  $li_class = [];

  if (array_key_exists('_class', $options)) {
    $li_class[] = $options['_class'];
    unset($options['_class']);
  }

  if (nav_active($nav_id)) {
    $li_class[] = 'active';
  }

  if (false !== $dropdown) {
    $li_class[] = 'JsHasDropdown';

    $script = KK_ENV_PROD ? '' : sfContext::getInstance()->getRequest()->getScriptName();
    $dropdown = str_replace('@', $script, $dropdown);
  }

  if (false !== $dropdown) {
    $text .= ' <span class="arrow"></span>';
  }

  $link = link_to($text, $internal_uri, $options);

  $li_class = $li_class ? ' class="'.implode(' ', $li_class).'"' : '';

  return "<li${li_class}>\n  ${link}\n${dropdown}\n</li>\n";
}

function staging_marker() {
  echo CORE_ENVIRONMENT === 'staging' ? '!bg-[#444]' : '';
}

?>
<div id="k-nav">
  <!-- mobile -->
  <nav id="k-nav_m" class="<?php echo staging_marker() ?>">
    <div id="k-nav_m_bar">
      <a class="k-nav_btn k-nav_m_btn_main JsNavToggle" id="k-slide-nav-btn">
        <i class="nav-h fa fa-bars"></i>
      </a>
      <?php echo link_to('&nbsp;', '@homepage', ['class' => 'k-nav_brand']) ?>
    </div>
  </nav>

  <!-- desktop -->
  <nav id="k-nav_d" class="<?php echo staging_marker() ?>">
    <div class="container">
<?php

    echo link_to('&nbsp;', '@homepage', ['class' => 'k-nav_brand']);

if (!$sf_user->isAuthenticated()) {
      
  echo "<ul class=\"k-nav_right\">\n";

  echo nav_item('-', 'Sign In', 'account/index', ['_class' => 'align-right signed-in']);

  echo "</ul>\n";

} else {

  echo "<ul>\n";

  echo nav_item('study-', 'Study', 'study/index', [], <<<DOM
<ul class="k-nav_dropdown">
  <li><a href="@/study">Browse</a></li>
  <li><a href="@/study/failedlist">Restudy List</a></li>
  <li><a href="@/study/mystories" class="active">My Stories</a></li>
</ul>
DOM
  );
  

  echo nav_item('review-', 'Review', '@overview', [], <<<DOM
<ul class="k-nav_dropdown">
  <li><a href="@/main">Spaced Repetition</a></li>
  <li><a href="@/review/custom">Kanji Review</a></li>
  <li><a href="@/review/vocab">Vocab Shuffle</a></li>
  <li><a href="@/members">Who's Reviewing?</a></li>
</ul>
DOM
  );

  echo nav_item('manage-', 'Flashcards', '@manage', [], <<<DOM
<ul class="k-nav_dropdown">
  <li><a href="@/manage">Manage Flashcards</a></li>
  <li><a href="@/manage/flashcardlist">Flashcard List</a></li>
</ul>
DOM
  );

  echo nav_item('more-', 'More', '@homepage', [], <<<DOM
<ul class="k-nav_dropdown">
  <li><a href="@/contact">Contact & Support</a></li>
  <li><a href="https://github.com/fabd/kanji-koohii/discussions">Discussions</a></li>
  <li><a href="@/about/support">Donate</a></li>
</ul>
DOM
  );
  echo "</ul>\n";

  echo "<ul class=\"k-nav_right\">\n";

  echo nav_item('about-learnmore', 'Help', '@learnmore');

  echo nav_item('about-support', '<span class="fa fa-heart"></span>', 'about/support', ['_class' => 'donate']);

  echo nav_item('account-', $sf_user->getUsername(), 'account/index', ['_class' => 'align-right signed-in'], <<<DOM
<ul class="k-nav_dropdown">
  <li><a href="@/account">Account Settings</a></li>
  <li><a href="@/profile">Member Profile</a></li>
  <li><a href="@/logout">Log out</a></li>
</ul>
DOM
  );

  echo "</ul>\n";
} ?>
      <div class="clear-both"></div>
    </div><!-- /container -->
  </nav>
</div>
<?php
// create the mobile menu data for the Aside component (cf. layout.php)

function nav_m_t($label, $id, $icon, $children) {
  return [
    'label'    => $label,
    'id'       => $id,
    'icon'     => $icon,
    'children' => $children
  ];
}

function nav_m_i($label, $id, $url) {
  $label  = link_to($label, $url);
  return [
    'label'    => $label,
    'id'       => $id
  ];
}

$nav_items = [];

if (!$sf_user->isAuthenticated()) {

  $nav_items[] = nav_m_i('Register',  'a-a',  'account/create');
  $nav_items[] = nav_m_i('Sign In',   'a-b',  '@login');

} else {

  $nav_items[] = nav_m_t('Study', 'study', 'fa-book', [
    nav_m_i('Index',       's-i',  'study/index'),
    nav_m_i('Restudy',     's-r',  'study/failedlist' ),
    nav_m_i('My Stories',  's-ms', 'study/mystories' ),
  ]);
    
  $nav_items[] = nav_m_t('Review', 'review', 'fa-signal', [
    nav_m_i('SRS',   'r-a', '@overview'),
    nav_m_i('Kanji', 'r-b', 'review/custom'),
    nav_m_i('Vocab', 'r-c', 'review/vocab')
  ]);

  $nav_items[] = nav_m_t('Flashcards', 'flashcards', 'fa-copy', [
    nav_m_i('Manage', 's-r', '@manage'),
    nav_m_i('List',   's-i', 'manage/flashcardlist')
  ]);

  $nav_items[] = nav_m_t('Account', 'account', 'fa-user', [
    nav_m_i('Settings',  'a-a',  'account/index'),
    nav_m_i('Profile',   'a-b',  'profile'),
    nav_m_i('Log out',   'a-c',  '@logout' )
  ]);

  $nav_items[] = nav_m_t('More', 'more', 'fa-ellipsis-h', [
    nav_m_i('Help',    'h-a',  '@learnmore'),
    nav_m_i('Contact', 'h-b',  '@contact'),
    nav_m_i('Donate',  'h-c',  'about/support' )
  ]);
}

$defaultOpen = 'study';

$pageToMenu = [
  'review-' => 'review',
  'manage-' => 'flashcards',
  'account-' => 'account', 
   'profile-' => 'account',
  'about-' => 'more', 'home-' => 'more'
];

foreach ($pageToMenu as $pageId => $open) {
  if (nav_active($pageId)) {
    $defaultOpen = $open; break;
  }
}

kk_globals_put('MBL_NAV_DATA', [
  'opened' => $defaultOpen,
  'items'  => $nav_items
]);
