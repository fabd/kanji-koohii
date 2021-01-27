<?php
$sf_request->setParameter('_homeFooter', true);
?>

<div class="pt-8"></div>

<?php include_partial('DeleteAccountConfirm'); ?>

<p><i class="fa fa-arrow-left mr-2"></i><?php echo link_to('Woops, take me back to the homepage!', '@homepage'); ?></p>