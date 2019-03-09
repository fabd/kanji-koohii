<div class="layout-home">

<?php include_partial('home/homeSide') ?>

<div class="pure-u-1 pure-u-md-3-5">

  <div class="app-header">
    <h2>Member Profile</h2>
    <div class="clear"></div>
  </div>

  <p> Sorry, the user <strong><?php echo esc_specialchars($sf_request->getParameter('username')) ?></strong> could not be found.</p>

  <p>What's next:</p>
  
  <ul>
      <li><a href="javascript:history.go(-1)">Go back to previous page</a></li>
      <li><?php echo link_to('Go to Homepage', '@homepage') ?></li>
  </ul>
 
</div>
