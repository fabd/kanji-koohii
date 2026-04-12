<div class="uiChartVs">
  <?php // using css3 now  div class="shadow"></div>?>
  <p class="left">
    <span class="ttl<?= $bZeroLeft ? ' zero' : ''; ?>"><?= $captionLeft; ?> <em>(<?= $valueLeft; ?>)</em></span>
    <span class="pct" style="width:<?= $pctLeft; ?>%"><?php if ($pctRight != 100): ?><span><?= $pctLeft; ?><em>%</em></span><?php endif; ?></span>
  </p>
  <p class="right">
    <span class="ttl<?= $bZeroRight ? ' zero' : ''; ?>"><?= $captionRight; ?> <em>(<?= $valueRight; ?>)</em></span>
    <span class="pct" style="width:<?= $pctRight; ?>%"><?php if ($pctLeft != 100): ?><span><?= $pctRight; ?><em>%</em></span><?php endif; ?></span>
  </p>
</div>
