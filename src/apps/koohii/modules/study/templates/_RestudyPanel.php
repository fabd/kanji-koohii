<?php
?>
<div class="ko-Box ko-Box--danger no-gutter-xs-sm dsk:mb-4 mbl:rounded-none">
  <div class="flex items-center mb-2">
    <h3 class="text-danger-darker font-bold leading-1 mb-0">Restudy</h3>
    <?= link_to('List', 'study/failedlist', ['class' => 'text-sm leading-1 ml-auto mbl:ml-4']); ?>
  </div>
  <div class="mbl:flex flex-wrap items-center justify-between">
    <p class="text-danger-darker text-sm mb-2 mbl:mb-0">
      <strong><?= $restudyCount; ?></strong> Forgotten Kanji
    </p>
<?= _bs_button(
  'Begin Restudy<i class="fa fa-book-open ml-2"></i>',
  'study/edit',
  ['query_string' => 'restudy', 'class' => 'ko-Btn ko-Btn--danger']
); ?>
  </div>
</div>
