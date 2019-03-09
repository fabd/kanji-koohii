<?php
  use_helper('SimpleDate');
  $sf_request->setParameter('_homeFooter', true);
?>

    <h2>By Month</h2>

  <div id="news-by-month">
    <ul>
<?php
  foreach (SitenewsPeer::getArchiveIndex() as $p) {
//  DBG::printr($p);exit;
    $label = simple_format_date(mktime(0,0,0,$p->month,1,$p->year), "M Y") . ' <span>('.$p->count.')</span>';
    echo '<li>'.link_to($label, 'news/index?year='.$p->year.'&month='.$p->month).'</li>'."\n";
  } ?>
    </ul>
  </div>
