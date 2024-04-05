<?php
  $sf_user_name = $sf_user->getUserName();
?>

<h2>New Password Confirmation</h2>

<p>
  A new password was sent to the email address that you used
  during registration on this website.
</p>
<p>
  The email should arrive  shortly.
</p>
<p>
  <?php echo _bs_button_to('Sign in','@login', ['query_string' => 'username='.$sf_user_name, 'class' => 'ko-Btn ko-Btn--success ko-Btn--large mr-2']) ?> with your new password.
</p>
 
      
