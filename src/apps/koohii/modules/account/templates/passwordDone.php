<?php
  use_helper('Form', 'Validation');
  $sf_request->setParameter('_homeFooter', true);
?>

    <h2>Password Updated!</h2>

    <?php echo form_errors() ?>

    <p> You are now <b>signed out</b>.</p>

    <div>
      <?php $sf_user->setAttribute('login_username', $username); // cf. redirectToLogin() ?>
      <?php echo _bs_button('Sign in with your new password','@login', array('query_string' => 'username='.$username, 'class' => 'btn btn-success btn-lg')) ?>
    </div>

