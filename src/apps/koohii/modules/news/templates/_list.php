<?php
  use_helper('Date');
  $css_is_home = isset($isHome) ? 'is-home' : '';
  $use_banner  = !isset($post_preview); // news/post
?>
<div id="sitenews" class="<?php echo $css_is_home ?>">
  <ul id="sitenews_list">
  <?php for ($i = 0; $i < count($posts); $i++) { $post = $posts[$i]; ?>
    <li>
      <h2><span class="newsdate"><?php echo date('j F Y', $post->date) ?></span><?php
        echo link_to($post->subject, '@news_by_id?id='.$post->id)
      ?></h2>
      <div class="clear"></div>
      <div class="bd content markdown<?php if ($i === 0) { echo ' is-first'; } ?>">
<?php echo $post->text ?>

    <?php if ($use_banner && $i === 0 && null === sfConfig::get('app_fork')): ?>
    <?php include_partial('news/_jpodBanner') ?>
    <?php endif ?>
      </div>
  <?php } ?>
    </li>
  </ul>
</div>