
<div id="browse" class="no-gutter-xs-sm mb-4 lg:rounded-[6px]">
  <div class="max-lg:flex items-center">

    <div class="max-lg:grow max-lg:mr-1 lg:mb-2">
        <?php echo input_tag('search', '', [
          'class'       => 'form-control',
          'maxlength'   => 32,
          'id'          => 'txtSearch',
          'placeholder' =>  'Enter number, kanji or keyword'
        ]); ?>
    </div>

    <div class="flex items-center -mx-1 max-lg:w-[182px] max-lg:ml-auto">
      <div class="w-1/2 mx-1">
<?php
  $prev = $framenum > 1 ? $framenum - 1 : 1;
  echo link_to('<i class="fa fa-chevron-left"></i>', 'study/edit?id='.$prev, ['class' => 'ko-Btn ko-Btn--search', 'accesskey' => 'p']);
?>
      </div>
        
      <div class="w-1/2 mx-1">
<?php
  $next = $framenum < rtkIndex::inst()->getNumCharacters() ? $framenum + 1 : 1;
  echo link_to('<i class="fa fa-chevron-right"></i>', 'study/edit?id='.$next, ['class' => 'ko-Btn ko-Btn--search', 'accesskey' => 'n']);
?>
      </div>
    </div>

  </div>
</div>
