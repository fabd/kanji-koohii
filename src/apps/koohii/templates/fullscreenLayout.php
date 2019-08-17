<!DOCTYPE html>
<html>
<head>
<?php include_http_metas() ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php include_metas() ?>
<?php include_title() ?>
<?php 
  // temporary (fix dirty js inclusions from labs mode later)
  $pageId = $sf_request->getParameter('module').'-'.$sf_request->getParameter('action');

  define('WEBPACK_ROOT', '/build/pack/');
  $build = (CORE_ENVIRONMENT === 'dev') ? '.raw' : '.min';

  // include Webpack bundles' extracted css
  if (CORE_ENVIRONMENT !== 'dev') {
    $sf_response->addStylesheet(implode([WEBPACK_ROOT,'root-bundle',$build,'.css']));
    $sf_response->addStylesheet(implode([WEBPACK_ROOT,'review-bundle',$build,'.css']));
  }

?>
<?php include_stylesheets() ?>
  <link href="https://use.fontawesome.com/releases/v5.0.1/css/all.css" rel="stylesheet">

  <!-- thx realfavicongenerator.net -->
  <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png?v=20170121b">
  <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png?v=20170121" sizes="32x32">
  <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png?v=20170121" sizes="16x16">
  <link rel="manifest" href="/favicons/manifest.json?v=20170121">
  <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg?v=20170121" color="#deb214">
  <link rel="shortcut icon" href="/favicons/favicon.ico?v=20170121">
  <meta name="apple-mobile-web-app-title" content="Koohii">
  <meta name="application-name" content="Koohii">
  <meta name="msapplication-TileColor" content="#f8c5e3">
  <meta name="msapplication-TileImage" content="/favicons/mstile-144x144.png?v=20170121">
  <meta name="msapplication-config" content="/favicons/browserconfig.xml?v=20170121">
  <meta name="theme-color" content="#f0ddd4"></head>


  <style type="text/css">
/* cancel the (absent) fixed nav padding */
body { padding-top:0;  }

<?php if(has_slot('inline_styles')): ?>
<?php include_slot('inline_styles') ?>
<?php endif ?>
  </style>
</head>
<body class="uiFcLayout yui-skin-sam">

<?php /*AjaxDebug (app.js)*/ if (CORE_ENVIRONMENT === 'dev'): ?><div id="AppAjaxFilterDebug" style="display:none"></div><?php endif ?>

<!--[if lt IE 9]><div id="ie"><![endif]-->

<?php echo $sf_content ?>

<!--[if lt IE 9]></div><![endif]-->

<?php
  // all 'first' so the bundles in view.yml files come last
  if (CORE_ENVIRONMENT !== 'dev') {
    $sf_response->addJavascript(implode([WEBPACK_ROOT,'vendors-bundle',$build,'.js']), 'first');  
  }
  $sf_response->addJavascript(implode([WEBPACK_ROOT,'root-bundle',$build,'.js']), 'first');
  $sf_response->addJavascript(implode([WEBPACK_ROOT,'review-bundle',$build,'.js']), 'first');
  $sf_response->addJavascript('/revtk/legacy-bundle.juicy.js', 'first');

  include_javascripts();
  if (has_slot('inline_javascript')) {
    echo "<script>\n" . get_slot('inline_javascript') . "</script>\n";
  }
?>

</body>
</html>
