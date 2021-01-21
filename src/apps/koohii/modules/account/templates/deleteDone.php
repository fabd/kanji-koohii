<?php
$sf_request->setParameter('_homeFooter', true);

?>

<div class="pt-8"></div>

  <h2>Account Deleted</h2>

  <div class="p-4 text-md rounded-lg bg-confirm text-confirm mb-8">
    <p>The account <?php echo "<strong>{$account_username}</strong>"; ?> has been succesfully removed.</p>
<?php if ($account_stats['stories'] > 0) { ?>
    <p><?php echo $account_stats['stories']; ?> stories deleted.</p>
<?php } ?>
<?php if ($account_stats['flashcards'] > 0) { ?>
    <p><?php echo $account_stats['flashcards']; ?> flashcards deleted.</p>
<?php } ?>
  </div>

<p class="text-md"><i class="fa fa-arrow-left mr-2"></i><?php echo link_to('Back to the homepage', '@homepage'); ?></p>
