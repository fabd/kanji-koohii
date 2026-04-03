
<div id="browse" class="no-gutter-xs-sm mb-4 lg:rounded-[6px]">
  <div class="max-lg:flex items-center">

    <div class="max-lg:grow max-lg:mr-1 lg:mb-2">
      <div class="ko-StudySearch relative">
        <div class="w-full h-[37px]"><!-- placeholder till JS kicks in --></div>
      </div>
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
