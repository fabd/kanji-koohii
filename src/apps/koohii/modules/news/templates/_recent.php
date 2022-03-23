<div class="row">
  <div class="col-lg-10 mx-auto">

<?php if (!KK_ENV_FORK) { include_partial('news/_jpodBanner'); } ?>

<h2>Blog</h2>

<?php include_partial('news/list', ['posts' => SitenewsPeer::getMostRecentPosts(), 'isHome' => true]) ?>

<div style="margin:2em 0; font-size:150%;">
  ...more in the <?php echo link_to('news archive','news/index') ?>.
</div>

  </div>
</div>