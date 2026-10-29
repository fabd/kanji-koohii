<div class="ko-Box">
<?php if (count($cards) > 0) { ?>
  <p class="mb-4"><strong>Hint!</strong> Click the keyword to open the Study page</p>
  <div id="JsSimpleReviewSummaryGrid"><!-- vue --></div>
<?php } else { ?>
  <p style="color:var(--color-red-500);">Woops, the session may have expired. The review summary is no longer available.</p>
<?php } ?>
</div>
