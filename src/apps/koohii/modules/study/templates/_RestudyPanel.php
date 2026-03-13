<?php
?>
<div class="ko-Box ko-Box--danger lg:mb-4 max-lg:flex max-lg:items-center max-lg:px-2 max-lg:py-2">
  <div class="lg:mb-2">
    <h3 class="text-[#BD2420] font-bold leading-none inline-block mb-0">Restudy</h3>
    <?= link_to('List', 'study/failedlist', ['class' => 'text-sm leading-none  ml-2 max-lg:ml-4']); ?>

    <div class="text-[#BD2420] text-sm">
      <strong><?= $restudyCount; ?></strong> Forgotten Kanji
    </div>
  </div>
  <div class="ml-auto">
<?= _bs_button_to(
  'Begin Restudy<i class="fa fa-book-open ml-2"></i>',
  'study/edit',
  ['query_string' => 'restudy', 'class' => 'ko-Btn ko-Btn--danger']
); ?>
  </div>
</div>
