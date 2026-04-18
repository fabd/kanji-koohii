<?php
use_helper('SimpleDate');
with_footer();
?>

    <h2>By Month</h2>

    <ul class="ko-NewsByMonth max-lg:flex flex-wrap max-md:text-sm">
<?php
  foreach (SitenewsPeer::getArchiveIndex() as $p) {
    //  DBG::printr($p);exit;
    $label = simple_format_date(mktime(0, 0, 0, $p->month, 1, $p->year), 'M Y').' <span>('.$p->count.')</span>';
    echo '<li class="max-lg:w-1/3">'.link_to($label, 'news/index?year='.$p->year.'&month='.$p->month).'</li>'."\n";
  } ?>
    </ul>
