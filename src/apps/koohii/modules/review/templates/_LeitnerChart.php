<div id="srs-home_actions" class="ux-flexButtonRow mb-4">
<?php
  if ($untested_cards > 0)
  {
    echo link_to("<strong>{$untested_cards}</strong> new", '@review', [
      'class' => 'ko-Btn ko-Btn--srs is-srs-new',
      'query_string' => query_string_for_review(['type' => 'untested']),
    ]);
  }

  if ($expired_total > 0)
  {
    echo link_to("<strong>{$expired_total}</strong> due", '@review', [
      'class' => 'ko-Btn ko-Btn--srs is-srs-due',
      'query_string' => query_string_for_review(['type' => 'expired']),
    ]);
  }

  if ($restudy_cards > 0)
  {
    echo link_to("<strong>{$restudy_cards}</strong> restudy", 'study/failedlist', [
      'class' => 'ko-Btn ko-Btn--srs is-srs-failed',
    ]);
  }
?>
</div>

<div id="JsLeitnerChartComponent" class="min-h-[268px] md:min-h-[298px] ko-Box no-gutter-xs-sm">
  <!-- Vue goes here -->
</div>

<?php
  kk_globals_put('LEITNER_CHART_DATA', $leitner_chart_data);
