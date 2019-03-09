<div class="uiChartVs">
  <?php #using css3 now  div class="shadow"></div>?>
  <p class="left">
    <span class="ttl<?php echo $bZeroLeft ? ' zero' : '' ?>"><?php echo $captionLeft ?> <em>(<?php echo $valueLeft ?>)</em></span>
    <span class="pct" style="width:<?php echo $pctLeft ?>%"><?php if($pctRight!=100): ?><span><?php echo $pctLeft ?><em>%</em></span><?php endif ?></span>
  </p>
  <p class="right">
    <span class="ttl<?php echo $bZeroRight ? ' zero' : '' ?>"><?php echo $captionRight ?> <em>(<?php echo $valueRight ?>)</em></span>
    <span class="pct" style="width:<?php echo $pctRight ?>%"><?php if($pctLeft!=100): ?><span><?php echo $pctRight ?><em>%</em></span><?php endif ?></span>
  </p>
</div>
