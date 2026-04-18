<?php
with_footer();

?>

<div class="pt-8"></div>

  <h2>Account Deleted</h2>

  <div class="p-4 text-md rounded-lg bg-confirm text-confirm mb-8">
    <p>The account <?= "<strong>{$account_username}</strong>"; ?> has been succesfully removed.</p>
<?php if ($account_stats['stories'] > 0) { ?>
    <p><?= $account_stats['stories']; ?> stories deleted.</p>
<?php } ?>
<?php if ($account_stats['flashcards'] > 0) { ?>
    <p><?= $account_stats['flashcards']; ?> flashcards deleted.</p>
<?php } ?>
<?php if ($account_stats['keywords'] > 0) { ?>
    <p><?= $account_stats['keywords']; ?> custom keywords deleted.</p>
<?php } ?>
  </div>

<p class="text-md"><i class="fa fa-arrow-left mr-2"></i><?= link_to('Back to the homepage', '@homepage'); ?></p>
