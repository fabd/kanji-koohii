<?php
?>
<div class="ko-Box ko-Box--danger no-gutter-xs-sm lg:mb-4 max-lg:rounded-none">
  <div class="flex items-center mb-2">
    <h3 class="text-[#BD2420] font-bold leading-[1] mb-0">Restudy</h3>
    <?= link_to('List', 'study/failedlist', ['class' => 'text-sm leading-[1] ml-auto max-lg:ml-4']); ?>
  </div>
  <div class="max-lg:flex flex-wrap items-center justify-between">
    <p class="text-[#BD2420] text-sm mb-2 max-lg:mb-0">
      <strong><?= $restudyCount; ?></strong> Forgotten Kanji
    </p>
<?= _bs_button_to(
  'Begin Restudy<i class="fa fa-book-open ml-2"></i>',
  'study/edit',
  ['query_string' => 'restudy', 'class' => 'ko-Btn ko-Btn--danger']
); ?>
  </div>
</div>
