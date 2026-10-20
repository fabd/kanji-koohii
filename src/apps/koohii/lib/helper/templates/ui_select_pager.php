<div class="uiPagerDiv">

<?php
  // build rows per page widget
  if ($pager->getMaxPerPage()) {
    $links = [];
    foreach ($pager->getMaxPerPageLinks() as $n) {
      $links[] = $pager->getMaxPerPageUrl($n);
    }

    $active = array_search($pager->getMaxPerPage(), $pager->getMaxPerPageLinks());

    echo ui_filter_std('Rows:', $links, $active !== false ? ['active' => $active] : []);
  }
?>

  <ul class="ko-Pager">
    <?php if ($p = $pager->getPreviousPage()): ?>
    <li class="ko-Pager-item ko-Pager-item--prev"><?= $pager->getPageLink($p, '&laquo;&nbsp;Previous'); ?></li>
  <?php else: ?>
    <li class="ko-Pager-item ko-Pager-item--prev is-disabled">&laquo;&nbsp;Previous</li>
  <?php endif; ?>
    
  <?php foreach ($pager->getLinks() as $p): ?>
    <?php if ($p === false): ?>
      <li class="ko-Pager-item ko-Pager-item--etc">&hellip;</li>
    <?php elseif ($p == $pager->getPage()): ?>
      <li class="ko-Pager-item is-active"><?= $p; ?></li>
    <?php else: ?>
      <li class="ko-Pager-item"><?= $pager->getPageLink($p); ?></li>
    <?php endif; ?>
  <?php endforeach; ?>

    <?php if ($p = $pager->getNextPage()): ?>
    <li class="ko-Pager-item ko-Pager-item--next"><?= $pager->getPageLink($p, 'Next&nbsp;&raquo;'); ?></li>
  <?php else: ?>
    <li class="ko-Pager-item ko-Pager-item--next is-disabled">Next&nbsp;&raquo;</li>
  <?php endif; ?>
  </ul>
  
  <div class="clear-both"></div>
</div>
