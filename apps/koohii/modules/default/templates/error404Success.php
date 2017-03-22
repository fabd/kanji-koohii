<?php include_partial('home/homeSide') ?>

<div class="pure-u-1 pure-u-md-3-5">

  <div class="app-header">
    <h2>Oops! We couldn't find this page for you.</h2>
    <div class="clear"></div>
  </div>

  <h3>Did you type the URL?</h3>
  
  <p> You may have typed the address (URL) incorrectly. Check it to make sure you've got the exact right spelling, capitalization, etc.</p>
  
  <h3> Did you follow a link from somewhere else at this site?</h3>
  
  <p> If you reached this page from another part of this site, please <?php echo link_to('let us know','@contact') ?> so we can correct our mistake.</p>
  
  <h3>Did you follow a link from another site?</h3>
  
  <p> Links from other sites can sometimes be outdated or misspelled. <?php echo link_to('Let us know','@contact') ?> where you came from and we can try to contact the other site in order to fix the problem.</p>

  <ul class="content">
      <li><a href="javascript:history.go(-1)">Go back to previous page</a></li>
      <li><?php echo link_to('Visit the home page', '@homepage') ?></li>
  </ul>

</div>
