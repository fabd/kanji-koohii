<?php
$sf_request->setParameter('_homeFooter', true);
?>
<div class="pt-8 markdown">

  <h2>Oops! Member Not Found</h2>

  <p>Woops, the user <strong><?php echo esc_specialchars($sf_request->getParameter('username')); ?></strong> could not be found.</p>

  <p>
    This user is not in the database. The username could be mistyped. The user may also have deleted their account.
  </p>

  <ul>
    <li><a href="javascript:history.go(-1)">Go back to previous page</a></li>
  </ul>

</div>