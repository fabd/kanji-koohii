<?php
  use_helper('Bootstrap', 'Form', 'Validation');
?>  
<h2>Delete Your Account</h2>

<p class="mb-2">Sorry to see you go! Thank you for checking out Kanji Koohii!</p>

<p>What did you think of the website? <?= link_to('Your feedback', '@contact', ['target' => '_blank']) ?> may help improve the site.</p>

<div class="padded-box rounded mb-8 ux-maxWidth360">

  <?php
  echo form_tag('account/delete', ['class' => '', 'autocomplete' => 'off']);

  echo _bs_form_group(
    ['validate' => 'email'],
    _bs_input_email('email', ['label' => 'Your email:'])
  );

  echo _bs_form_group(
    ['validate' => 'confirm_text'],
    _bs_input_text('confirm_text', ['label' => '<strong>To verify, type</strong> <span class="font-normal font-italic">delete my account</span> below:', 'class' => ''])
  );

  echo _bs_form_group(
    ['validate' => 'password'],
    _bs_input_password('password', ['label' => 'Confirm your password:', 'class' => ''])
  );

  ?>
  <p class="mt-8 mb-2 text-danger">
    <strong>Account deletion is final</strong>. There will be no way to restore your account.
  </p>
  <?php
  echo _bs_form_group(
    _bs_submit_tag('Delete this account', ['class' => 'btn-danger'])
  );
  ?>
  </form>

  <?php DBG::request(); ?>
</div>