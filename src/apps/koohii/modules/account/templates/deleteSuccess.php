<?php
$sf_request->setParameter('_homeFooter', true);
?>

<div class="pt-8"></div>

<?php if ($sf_request->hasParameter('confirmation')) { ?>

  <h2>Account Deleted</h2>

  <div class="p-4 text-md rounded-lg bg-confirm text-confirm mb-8">
    <p>The account has been succesfully removed.</p>
  </div>

<p class="text-md"><i class="fa fa-arrow-left mr-2"></i><?php echo link_to('Back to the homepage', '@homepage'); ?></p>


<?php }
else
{ ?>

<?php include_component('account', 'DeleteAccountConfirm'); ?>
<p><i class="fa fa-arrow-left mr-2"></i><?php echo link_to('Woops, take me back to the homepage!', '@homepage'); ?></p>

<?php } ?>
