<?php
use_helper('Date');
$css_is_home = isset($isHome) ? 'is-home' : '';
?>
<div class="ko-SiteNews <?= $css_is_home; ?>">
  <ul class="ko-SiteNews-list">
  <?php for ($i = 0; $i < count($posts); $i++) {
    $post = $posts[$i]; ?>
    <li>
      <h2 class="ko-SiteNews-hd mb-5 pt-1 pb-2">
        <span class="ko-SiteNews-date"><?= date('j F Y', $post->date); ?></span>
        <?= link_to($post->subject, '@news_by_id?id='.$post->id);
    ?>
      </h2>
      <div class="ko-SiteNews-body content markdown<?php if ($i === 0) {
        echo ' is-first';
      } ?>">
<?= $post->text; ?>

      </div>
  <?php } ?>
    </li>
  </ul>
</div>