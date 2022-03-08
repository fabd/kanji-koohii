<?php
  $sf_request->setParameter('_homeFooter', true);
?>

<div class="row">

  <div class="col-md-3 mb-8">
    <div class="kk-DocNav">
      <?= $tocHtml; ?>
    </div>
  </div>
  
  <div class="col-md-9 markdown kk-DocMain">
    <?= $docHtml; ?>
  </div>

</div>
