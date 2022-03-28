<?php
?>
<div class="ko-Box ko-Box--danger ko-Box--stroke no-gutter-xs-sm dsk:mb-4">
  <div class="flex items-center mb-2">
    <h3 class="text-danger-dark font-bold leading-1 mb-0">Restudy</h3>
    <?= link_to('List', 'study/failedlist', ['class' => 'text-sm ml-auto']); ?>
  </div>
  <p class="text-danger-darker text-sm mb-2">
    <strong><?= $restudyCount; ?></strong> Forgotten Kanji
  </p>
<?= _bs_button(
  'Begin Restudy<i class="fa fa-book-open ml-2"></i>',
  'study/edit',
  ['query_string' => 'restudy', 'class' => 'ko-Btn ko-Btn--danger']
); ?>
</div>