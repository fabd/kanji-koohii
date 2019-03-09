<?php 
  // $ucsId    = ReviewsPeer::getNextUnlearnedKanji($sf_user->getUserId());
  // $kanji    = KanjisPeer::getKanjiByUCS($ucsId);
  // $next_uri = 'study/edit?id='.$kanji->kanji;
 ?>

<div id="study-restudy" class="study-action-comp no-gutter-xs-sm mb-d-1">
  <div class="flex flex-g-s">

    <div class="col-m col-d-12 col-g flex-a-c">
      <h3>Restudy <span><?php echo $restudy_count ?></span></h3>
    </div>

    <div class="col-m-1 col-d-6 col-g">
      <?php echo link_to('Start', 'study/edit', array('query_string' => "restudy", 'class' => 'btn btn-primary')) ?>
    </div>

  </div>
</div>


<?php /*
  <div class="frame">
    <ul>
<?php foreach(ReviewsPeer::getRestudyQuickList($sf_user->getUserId()) as $R): ?>
      <li<?php echo $R['seq_nr']==$framenum ? ' class="selected"' : '' ?>>
        <span><?php echo $R['seq_nr'] ?></span>
        <?php $kw = preg_replace('/\//', '<br/>', $R['keyword']); echo link_to($kw, 'study/edit?id='.$R['seq_nr']) ?>
      </li>
<?php endforeach ?>
    </ul>
    <div class="clear"></div>
  </div>
*/
