<?php
slot('SideNavActive', $active);

$_routing = sfContext::getInstance()->getRouting();
$_internal_uri = $_routing->getCurrentInternalUri();

function output_sidenav_item($text, $internal_uri)
{
  $options = array(); //)get_slot('SideNavActive') === $id ? array('class' => 'active') : array();
  $uri     = $internal_uri . '#' . strtr( ltrim($text, '/'), '/', '-');
  echo tag('li', $options, true) . link_to($text, $uri) . '</li>';
}


?>
<div style="position:fixed">

<div class="side-menu">
  <h2>The Kanji Koohii API</h2>
  <ul>
    <?php output_sidenav_item('Overview', $_internal_uri) ?></li>
    <?php output_sidenav_item('Terms of Use', $_internal_uri) ?></li>
    <?php output_sidenav_item('Obtaining your API key', $_internal_uri) ?></li>
  </ul>
</div>

<div class="side-menu">
  <h2>API Usage</h2>
  <ul>
    <?php output_sidenav_item('Glossary', $_internal_uri) ?></li>
    <?php output_sidenav_item('Request Format', $_internal_uri) ?></li>
    <?php output_sidenav_item('Response Format', $_internal_uri) ?></li>
  </ul>
</div>

<div class="side-menu">
  <h2>API Methods</h2>
  <ul>
    <?php output_sidenav_item('/account/info', $_internal_uri) ?></li>

    <!--<?php output_sidenav_item('/keywords/list', $_internal_uri) ?></li>-->

    <!-- getlist (timestamp) since, pour cacher les cartes -->
    <!--<?php output_sidenav_item('/flashcards/list', $_internal_uri) ?></li>-->

    <?php output_sidenav_item('/review/start', $_internal_uri) ?></li>
    <?php output_sidenav_item('/review/fetch', $_internal_uri) ?></li>
    <?php output_sidenav_item('/review/sync',  $_internal_uri) ?></li>

    <?php output_sidenav_item('/srs/info', $_internal_uri) ?></li>

    <!--<?php output_sidenav_item('/stories/list', $_internal_uri) ?></li>-->

    <?php output_sidenav_item('/study/sync', $_internal_uri) ?></li>
    <?php output_sidenav_item('/study/info', $_internal_uri) ?></li>

  </ul>
</div>

</div>
