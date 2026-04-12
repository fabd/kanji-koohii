<!DOCTYPE html>
<html>
<head>
<?php include_http_metas(); ?>
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, minimum-scale=1, maximum-scale=1">
<?php include_metas(); ?>
<?php if (CORE_ENVIRONMENT === 'staging') {
  echo '<meta name="robots" content="noindex, nofollow" />'."\n";
} ?>
<?php include_title(); ?>
  <link rel="alternate" type="application/rss+xml" title="RSS" href="rss">
<?php
$pageId        = $sf_request->getParameter('module').'-'.$sf_request->getParameter('action');
$isLandingPage = $sf_request->getParameter('isLandingPage');
$withFooter    = $sf_request->getParameter('_homeFooter') ? 'with-footer ' : '';

// call it here at last, if it wasn't already triggered via view.yml (addJavascript())
$sf_response->addViteEntries();

include_stylesheets();
include_javascripts();
include_fontawesome();
?>

<!-- ~*~*~*~*~*~*~*~*~*~*~*~* HANAMI ~*~*~*~*~*~*~*~*~*~*~*~* -->
<?php if ($pageId === 'home-index'): ?>
<script type="text/javascript" src="/koohii/hanami.js?v=20260328" defer="1"></script>
<style>
#k-nav_d, #k-nav_m { background: linear-gradient(45deg, #216daa, #882761) !important; }
.petal {
  position: fixed;
  top: 0; left: 0;
  pointer-events: none;
  will-change: transform;
  z-index: 9999;
  clip-path: polygon(50% 15%, 70% 0%, 90% 10%, 100% 35%, 100% 70%, 50% 100%, 0% 70%, 0% 35%, 10% 10%, 30% 0%);
  border-radius: 20% 20% 50% 50% / 10% 10% 80% 80%;
}
</style>
<?php endif; ?>
<!-- ~*~*~*~*~*~*~*~*~*~*~*~* HANAMI ~*~*~*~*~*~*~*~*~*~*~*~* -->

  <!-- thx realfavicongenerator.net -->
  <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png?v=20170121">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png?v=20170121">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png?v=20170121">
  <link rel="manifest" href="/favicons/site.webmanifest?v=20170121">
  <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg?v=20170121" color="#deb214">
  <link rel="shortcut icon" href="/favicons/favicon.ico?v=20170121">
  <meta name="apple-mobile-web-app-title" content="Koohii">
  <meta name="application-name" content="Koohii">
  <meta name="msapplication-TileColor" content="#f8c5e3">
  <meta name="msapplication-TileImage" content="/favicons/mstile-144x144.png?v=20170121">
  <meta name="msapplication-config" content="/favicons/browserconfig.xml?v=20170121">
  <meta name="theme-color" content="#f0ddd4">


<?php if (has_slot('inline_styles')): ?>
  <style type="text/css">
<?php include_slot('inline_styles'); ?>
  </style>
<?php endif; ?>
<?php if (KK_ENV_PROD) {
  use_helper('__Analytics');
  /* async */ echo ga_tracking_code();
} ?>
</head>
<body
  class="<?= $withFooter; ?> <?php $pageId = $sf_request->getParameter('module').'-'.$sf_request->getParameter('action');
echo $pageId; ?>">
  <div id="body-navbar-holder"></div>

<div id="aside-component"></div><!-- fabd : FIXME?? TS refactor Vue3 -->

<?php include_partial('global/navbar', ['pageId' => $pageId]); ?>

<?php if ($isLandingPage) {
  echo $sf_content;
} else { ?>
<main>
  <div class="ko-MainContainer ko-Container">
<?= $sf_content; ?>
  </div>
</main>
<?php if ($sf_request->getParameter('_homeFooter')) {
  include_partial('home/homeFooter');
} ?>
<?php } ?>

<?php kk_globals_out(); ?>
<?php koohii_onload_slots_out(); ?>

<?php if (KK_ENV_DEV):  ?>
<script>
  // auto-collapse sf debug bar
  window.addEventListener("load", function(ev){
    if (typeof sfWebDebugToggleMenu !== "undefined") {
      sfWebDebugToggleMenu();
    }
  });
</script>
<?php endif; ?>

<div id="__debug_log"></div>
<?php if ($sf_user->getUserName() === 'fuaburisu' || $sf_user->isAdministrator()) {
  $db = kk_get_database();
  if ($db->getProfiler()) {
    echo $db->getProfiler()->getDebugLog();
  }
  echo sfProjectConfiguration::getActive()->getAdminInfoFooter();
} ?>

<?php /*
  <div id="footer">
    <p>
    <?php echo link_to('home', '@homepage') ?>&nbsp;|
    <?php echo link_to('about', 'about/index') ?>&nbsp;|
    <?php echo link_to('contact', '@contact') ?>&nbsp;|
    User contributions licensed under <?php echo link_to('CC-BY-SA-NC', 'http://creativecommons.org/licenses/by-nc-sa/3.0/') ?> <?php echo link_to('with attribution', 'about/license') ?>
    |&nbsp;<span id="dbgtime"><?php echo sfProjectConfiguration::getActive()->profileEnd() ?>s</span>
    </p>
  </div>
*/ ?>

</body>
</html>
