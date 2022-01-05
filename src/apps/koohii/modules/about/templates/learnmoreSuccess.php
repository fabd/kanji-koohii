<?php
  $sf_request->setParameter('_homeFooter', true);
?>

  <div id="learn-more">
  
    <div class="row">
  
      <div class="col-md-3 mb-8">
        <div class="kk-DocNav">
          <!-- <h2>Contents</h2> -->
  
          <?= $tocHtml; ?>
  
        </div>
      </div>
  
      <div class="col-md-9 markdown kk-DocMain">
  <?= $docHtml; ?>
      </div><!-- /col -->
  
    </div><!-- /row -->
  
  </div>
  
