
<div id="browse" class="no-gutter-xs-sm mb-d-1">
  <div class="flex flex-g-s">

    <div class="study-search_input col-m col-d-12 col-g mb-d-p50">
        <?php echo input_tag('search', '', array(
          'class'       => 'form-control',
          'maxlength'   => 32,
          'id'          => 'txtSearch',
          'placeholder' =>  'Enter number, kanji or keyword'
        )); ?>
    </div>

    <div class="col-m-2 col-d-6 col-g">
    <?php
        $prev = $framenum > 1 ? $framenum - 1 : 1;
        echo link_to('<i class="fa fa-chevron-left"></i>', 'study/edit?id='.$prev, array('class' => 'study-search_btn is-prev', 'accesskey' => 'p'));
    ?>
    </div>
        
    <div class="col-m-2 col-d-6 col-g">
    <?php
        $next = $framenum < rtkIndex::inst()->getNumCharacters() ? $framenum + 1 : 1;
        echo link_to('<i class="fa fa-chevron-right"></i>', 'study/edit?id='.$next, array('class' => 'study-search_btn is-next', 'accesskey' => 'n'));
    ?>
    </div>

  </div>
</div>
