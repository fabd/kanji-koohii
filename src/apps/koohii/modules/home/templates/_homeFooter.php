<?php
/**
 * Encode url and text for Twitter buttons.
 *
 *   https://twitter.com/intent/tweet?url={url}&amp;text={text}
 */
function koohii_tweet_query()
{
  return http_build_query(['url' => sfConfig::get('app_website_url'), 'text' => 'Kanji Koohii']);
}

use_helper('Widgets');
?>

<footer class="ko-PageFooter">
  <div class="ko-PageFooter-dots"></div>
  <div class="ko-PageFooter-grad">
    <div class="ko-Container pt-6">
      <p class="mb-2">Made in Belgium since 2006 by&nbsp;Fabrice.</p>

        <?php if ($sf_user->isAuthenticated() && !KK_ENV_FORK): ?>
<p class="is-support mb-4">
  <strong>Support my work</strong> with <?= link_to('Patreon, PayPal', 'about/support'); ?>, <span class="max-sm:block">and affiliate <?php use_helper('__Sponsor');
          echo link_to_jpod101('JapanesePod101.com'); ?>.</span>
</p>
        <?php endif; ?>

<ul class="ko-PageFooter-links list-none text-sm p-0 flex justify-center">
  <li><?= link_to('<i class="fa fa-comment"></i>Blog', 'news/index'); ?></li>
  <li><?= link_to('<i class="fa fa-envelope"></i>Contact', '@contact'); ?></li>
  <li><?= link_to('<i class="fa fa-question"></i>About', 'about/index'); ?>
</ul>
    </div>
  </div>
</footer>
