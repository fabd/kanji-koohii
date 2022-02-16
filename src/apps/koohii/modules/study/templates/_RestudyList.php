<?php 
  // $ucsId    = ReviewsPeer::getNextUnlearnedKanji($sf_user->getUserId());
  // $kanji    = KanjisPeer::getKanjiByUCS($ucsId);
  // $next_uri = 'study/edit?id='.$kanji->kanji;
 ?>

<div id="study-restudy" class="study-action-comp no-gutter-xs-sm md:mb-4">
  <div class="flex flex-g-s">

    <div class="col-m col-d-12 col-g flex-a-c">
      <h3>Restudy <span><?php echo $restudy_count ?></span></h3>
    </div>

    <div class="col-m-1 col-d-6 col-g">
      <?php echo link_to('Start', 'study/edit', ['query_string' => "restudy", 'class' => 'btn btn-primary']) ?>
    </div>

  </div>
</div>
