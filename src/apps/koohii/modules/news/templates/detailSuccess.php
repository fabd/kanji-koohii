<?php 
  $post_id = $posts ? $posts[0]->id : 0; 
?>
<div class="row">

  <div class="col-md-9">

<?php if ($posts): ?>

  <?php $sf_response->setTitle($posts[0]->subject); ?>
  <?php include_partial('news/list', ['posts' => $posts]); ?>

<?php else: ?>

  <div class="formerrormessage">
    Oops, this news post can not be found.
  </div>

<?php endif ?>

    <div id="sitenews_back">
      <?php echo _bs_button('&laquo; Back', '@homepage', ['class' => 'btn btn-success']) ?>
      <?php if ($sf_user->getUserName() === 'fuaburisu' || $sf_user->isAdministrator()): ?>
        <?php echo '&nbsp;&nbsp;'.link_to('<i class="fa fa-edit"></i> Edit Post', "news/post?post_id=$post_id", ['class' => 'btn btn-ghost']) ?>
      <?php endif; ?>

    </div>

  </div><!-- /col -->

  <div class="col-md-3">
    <?php include_partial('archiveList') ?>
  </div>

</div>
