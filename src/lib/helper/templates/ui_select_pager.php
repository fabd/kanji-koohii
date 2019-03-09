<div class="uiPagerDiv">

<?php
  // build rows per page widget
  if ($pager->getMaxPerPage())
  {
    $links = array();
    foreach($pager->getMaxPerPageLinks() as $n) {
      $links[] = $pager->getMaxPerPageUrl($n);
    }

    $active = array_search($pager->getMaxPerPage(), $pager->getMaxPerPageLinks());

    echo ui_filter_std('Rows:', $links, $active!==false ? array('active' => $active) : array());
  }
?>

  <ul class="uiPager">
    <?php if ($p = $pager->getPreviousPage()): ?>
    <li class="prev"><?php echo $pager->getPageLink($p, '&laquo;&nbsp;Previous') ?></li>
  <?php else: ?>
    <li class="prev disabled">&laquo;&nbsp;Previous</li>
  <?php endif ?>
    
  <?php foreach($pager->getLinks() as $p): ?>
    <?php if ($p===false): ?>
      <li class="etc">&hellip;</li>
    <?php elseif ($p==$pager->getPage()): ?>
      <li class="active"><?php echo $p ?></li>
    <?php else: ?>
      <li><?php echo $pager->getPageLink($p) ?></li>
    <?php endif ?>
  <?php endforeach ?>

    <?php if ($p = $pager->getNextPage()): ?>
    <li class="next"><?php echo $pager->getPageLink($p, 'Next&nbsp;&raquo;') ?></li>
  <?php else: ?>
    <li class="next disabled">Next&nbsp;&raquo;</li>
  <?php endif ?>
  </ul>
  
  <div class="clear"></div>
</div>
