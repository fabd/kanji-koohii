<?php $css_is_home = isset($isHome) ? ' is-home' : ''; ?>
<div id="sitenews" class="<?php echo $css_is_home ?>">
  <ul>
  <?php for ($i = 0; $i < count($posts); $i++) { $post = $posts[$i]; ?>
    <li>
      <h3><span class="newsdate"><?php echo $post->date ?></span><?php echo $post->link ?></h3>
      <div class="clear"></div>
      <div class="bd content <?php if ($i === 0) { echo ' is-first'; } ?>">
    <?php echo $post->text ?>

    <?php if ($i === 0 && null === sfConfig::get('app_fork')): ?>
    <?php include_partial('news/_jpodBanner') ?>
    <?php endif ?>
      </div>
  <?php } ?>
    </li>
  </ul>
</div>