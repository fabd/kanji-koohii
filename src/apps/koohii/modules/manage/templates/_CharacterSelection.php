<?php use_helper('CJK') ?>
<div id="mng-cards" class="padded-box rounded">
 <div class="scroll">
  <?php foreach($cards as $ucsId): ?>
    <div class="fc">
      <?php $c = rtkIndex::getCharForIndex($ucsId); echo cjk_lang_ja($c); ?>
      <div class="n"><?php 
        $frameNr = rtkIndex::getIndexForChar($c);
        echo $frameNr !== false ? '#'.$frameNr : '(not in index)';
      ?></div>
    </div>
  <?php endforeach; ?>
  <div class="clear-both"></div>
 </div>
</div>

