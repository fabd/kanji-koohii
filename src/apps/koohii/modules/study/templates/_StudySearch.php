
<div id="browse" class="no-gutter-xs-sm md:mb-4">
  <div class="mbl:flex items-center">

    <div class="study-search_input mbl:grow dsk:mb-2">
        <?php echo input_tag('search', '', [
          'class'       => 'form-control',
          'maxlength'   => 32,
          'id'          => 'txtSearch',
          'placeholder' =>  'Enter number, kanji or keyword'
        ]); ?>
    </div>

    <div class="flex items-center -mx-1 mbl:w-[182px] mbl:ml-auto">
      <div class="w-1/2 mx-1">
<?php
        $prev = $framenum > 1 ? $framenum - 1 : 1;
        echo link_to('<i class="fa fa-chevron-left"></i>', 'study/edit?id='.$prev, ['class' => 'study-search_btn is-prev', 'accesskey' => 'p']);
?>
      </div>
        
      <div class="w-1/2 mx-1">
<?php
        $next = $framenum < rtkIndex::inst()->getNumCharacters() ? $framenum + 1 : 1;
        echo link_to('<i class="fa fa-chevron-right"></i>', 'study/edit?id='.$next, ['class' => 'study-search_btn is-next', 'accesskey' => 'n']);
?>
      </div>
    </div>

  </div>
</div>
