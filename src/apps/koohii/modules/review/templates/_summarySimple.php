<?php use_helper('Widgets', 'SimpleDate', 'Form', 'CJK', 'Links'); ?>

<?php //DBG::request()?>

<?php //echo DBG::printr($cards);?>

<div id="fm-summary" class="ko-Box">
<?php if (count($cards) > 0) { ?>

  <p><strong>Hint!</strong>  Click the <?= _CJ('kanji'); ?> to open the Study page</p>
  <ul>
  <?php foreach ($cards as $c) { ?>
    <li class="card<?= $c->pass ? '' : ' fail'; ?>">
      <div class="rbn"></div>
      <div class="chr"><?= link_to(cjk_lang_ja($c->kanji), '@study_edit?id='.$c->framenum, ['target' => '_blank']); ?></div>
      <div class="inf">#<?= $c->framenum.'<em>'.$c->keyword; ?></em></div>
    </li>
  <?php } ?>
  </ul>

<?php }
else
{ ?>
  <p style="color:red;">Woops, the session may have expired. The review summary is no longer available.</p>
<?php } ?>
</div>

