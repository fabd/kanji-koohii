<?php
use_helper('Form', 'Validation');
$sf_request->setParameter('_homeFooter', true);
?>

    <h2>Password Updated!</h2>

    <?= form_errors(); ?>

    <p> You are now <b>signed out</b>.</p>

    <div>
      <?php $sf_user->setAttribute('login_username', $username); // cf. redirectToLogin()?>
      <?= _bs_button_to('Sign in with your new password', '@login', ['query_string' => 'username='.$username, 'class' => 'ko-Btn ko-Btn--success ko-Btn--large']); ?>
    </div>

