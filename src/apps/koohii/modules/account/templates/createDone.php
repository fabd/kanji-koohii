<?php
  use_helper('Form', 'Validation', 'Widgets');
  $sf_request->setParameter('_homeFooter', true);
?>
    <h2>Welcome <?php echo $username ?>, your account is ready!</h2>

    <h3>Note about <em>Remembering the Kanji</em> edition</h3>
    <p>
      Your account is configured for the <strong>6th edition</strong> of Remembering the Kanji,
      Volume 1.
    </p>

    <p>
      <strong>If you have the 5th edition (or older)</strong> of the <?php echo _CJ('Remembering the Kanji') ?> book(s), 
      after signing in go to the <strong>Account</strong> settings and select "Old Edition".
    </p>

    <div>
      <?php $sf_user->setAttribute('login_username', $username); // cf. redirectToLogin() ?>
      <?php echo _bs_button('Sign in','@login', ['query_string' => 'username='.$username, 'class' => 'ko-Btn ko-Btn--success btn-lg']) ?>
    </div>

    <?php echo form_errors() ?>
 
</div>

