<?php
  $sf_response->setTitle('Kanji Koohii API');

  slot('docute.init');

  // docute.init() configuration in javascript
?>
{
  home: '/koohii/api/README.md',

  nav: [
    // homepage
    {title: 'API', path: '/'},

    {title: 'Home', path: 'http://kanji.koohii.com'}
  ]
}
<?php end_slot(); ?>
