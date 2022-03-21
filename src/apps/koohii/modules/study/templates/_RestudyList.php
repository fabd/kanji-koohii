<?php 
  // $ucsId    = ReviewsPeer::getNextUnlearnedKanji($sf_user->getUserId());
  // $kanji    = KanjisPeer::getKanjiByUCS($ucsId);
  // $next_uri = 'study/edit?id='.$kanji->kanji;
 ?>

<div id="study-restudy" class="study-action-comp no-gutter-xs-sm dsk:mb-4">
  <div class="mbl:flex items-center">

    <h3 class="">Restudy <strong><?php echo $restudy_count ?></strong></h3>

    <div class="mbl:ml-auto">
      <?php echo link_to('Start', 'study/edit', ['query_string' => "restudy", 'class' => 'ko-Btn is-ghost']) ?>
    </div>

  </div>
</div>
