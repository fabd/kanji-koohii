
<div class="row">

  <div class="col-md-9">

<?php if ($posts): ?>

  <?php $sf_response->setTitle($posts[0]->subject); ?>
  <?php include_partial('news/list', array('posts' => $posts)); ?>

<?php else: ?>

  <div class="formerrormessage">
    Oops, this news post can not be found.
  </div>

<?php endif ?>

    <div id="sitenews_back">
      <?php echo _bs_button('&laquo; Back', '@homepage', array('class' => 'btn btn-success')) ?>
    </div>

  </div><!-- /col -->

  <div class="col-md-3">
    <?php include_partial('archiveList') ?>
  </div>

</div>
