<?php
  use_helper('Widgets');
?>

  <div id="srs-home_actions">
      <?php # link_to('<i class="fa fa-cog"></i>', 'account/spacedrepetition', ['class' => 'uiGUI btn btn-ghost btn-lg btn-align-right']) ?>

<?php
// test for small screens
// $untested_cards = 100;
// $expired_total = 999;
// $restudy_cards = 299;

  if ($untested_cards > 0) {
    echo _bs_button("<strong>$untested_cards</strong> new", '@review', [
      'class' => 'btn btn-lg btn-srs btn-new',
      'query_string' => $me->getQueryString(['type' => 'untested'])
    ]);
  }

  if ($expired_total > 0) {
    echo _bs_button("<strong>$expired_total</strong> due", '@review', [
      'class' => 'btn btn-lg btn-srs btn-due',
      'query_string' => $me->getQueryString(['type' => 'expired'])
    ]);
  }

  if ($restudy_cards > 0) {
    echo _bs_button("<strong>$restudy_cards</strong> restudy", 'study/failedlist', [
      'class' => 'btn btn-lg btn-srs btn-failed'
    ]);
  }
?>
    <div class="clear"></div>
  </div>

  <?php /* OBSOLETE
  <div class="filters">
      $links = array(
        array('Simple', '', array('class' => 'uiFilterStd-s')),
        array('Full', '', array('class' => 'uiFilterStd-f'))
      );
      echo ui_filter_std('View:', $links, array('class' => 'mode-toggle', 'active' => 0));
  </div>
  */  ?>

  <div class="clear"></div>

  <div id="app-vue">

    <div id="leitner-chart_pane" class="padded-box no-gutter-xs-sm">
      <!-- container-id is used by the component to determine available width -->
      <leitner-chart container-id="leitner-chart_pane"></leitner-chart>
    </div>
      
  </div>


<?php koohii_onload_slot() ?>
// syntax highlight my <script> :)
"use strict";

  var leitner_chart_data = <?php echo json_encode($leitner_chart_data /*, JSON_PRETTY_PRINT*/); ?>;

  var vm = new Vue({
    el: '#app-vue',
    components: {
      LeitnerChart: Koohii.UX.LeitnerChart
    },
  });
<?php end_slot() ?>