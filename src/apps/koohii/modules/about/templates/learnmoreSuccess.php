<?php
  $sf_request->setParameter('_homeFooter', true);
?>

<div class="row">

  <div class="col-md-3 mb-8">
    <div class="ko-DocNav">
      <?= $tocHtml; ?>
    </div>
  </div>
  
  <div class="col-md-9 markdown ko-DocMain">
    <?= $docHtml; ?>
  </div>

</div>
