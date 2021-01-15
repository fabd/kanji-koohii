<div id="due-cards" class="padded-box no-gutter-xs-sm mb-3">
  <div class="barchart-container">
    <div class="barchart-chart">
      <div class="barchart-grid"></div>
      <ul class="barchart-cols">
        <?php for($day = 1; $day <= DueCardsGraphComponent::GRAPH_DAYS; $day++) {
      $numCards  = $cardsByDay[$day];
      $cssHeight = $maxCards > 0 ? ($numCards / $maxCards) * 100 : 0;
      $cssHeight = $cssHeight > 0 ? $cssHeight.'%' : '4px';    
      ?><li>
          <div class="bar<?php echo $numCards ? '' : ' zero' ?>" style="height:<?php echo $cssHeight ?>;">
            <span class="lbl"><?php echo $day === 1 ? '<strong>Tomorrow</strong>' : $day.' days' ?></span>
            <span class="val"><?php echo $numCards ?></span>
          </div>
        </li><?php } ?>
      </ul>
    </div>
  </div>
</div>

