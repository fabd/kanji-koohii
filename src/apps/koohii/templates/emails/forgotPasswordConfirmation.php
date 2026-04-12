Hi <?= $username; ?>,

A request for a new password was sent to this address.

Because we can not read your current password, a new random password
is generated for you, that allows you to log back into the site.

Please sign in at:
 
  <?= url_for('@login', true); ?>
 
 
With the following information:
 
  Username :  <?= $username."\n"; ?>
  Password :  <?= $password."\n"; ?>

After you signed in, it is highly recommended that you go to your account page
and update the password to something personal and more secure:
 
  <?= url_for('account/password', true); ?>
 

F.A.Q.

=> Did you use the same email address for multiple accounts?

   Please contact the webmaster through the Contact page.
