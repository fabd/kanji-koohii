<?php
  use_helper('Links');
  $sf_request->setParameter('_homeFooter', true);
?>
<div class="row">
 <div id="support-koohii" class="col-md-8 col-md-offset-2">

  <h2>Support Kanji Koohii<span class="visible-md-lg"> Development</span></h2>

  <p>
Maintaining and developing new features takes a considerable amount of time. Your support is very important and could allow me to free up more time for development. <em>Thank you!</em>
  </p>

  <div class="padded-box-inset">
    <a class="block" href="https://www.patreon.com/kanjikoohii" target="_blank">
      <img style="width:126px" src="https://s3.amazonaws.com/patreon_public_assets/toolbox/patreon.png"><br>
      <br>
      <strong>Support  Kanji Koohii development on Patreon (recurring pledge)</strong>
    </a>
  </div>

  <div class="padded-box-inset mt-2 mb-2">
    <a class="block" href="https://paypal.me/koohii">
      <img style="width:126px" src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-200px.png"><br>
      <br>
      <strong>Donate via PayPal (one time)</strong>
    </a>
  </div>

  <h3>Current patrons</h3>

  <p><strong>Thank you for supporting Kanji Koohii!</strong>  <a class="become-a-patron" href="https://www.patreon.com/bePatron?u=4987873">Become&nbsp;a&nbsp;patron</a><!-- and enjoy the perks--></p>

  <?php $patrons = PatreonPeer::getPatronsList(); ?>
  <ul id="patreon-patrons">
  <?php //DBG::printr($patrons); ?>
<?php foreach ($patrons as $pa) {
    $display_name = !empty($pa['username']) ? link_to_member($pa['username']) : escape_once($pa['pa_full_name']);
    echo '<li><span class="ws-nw">'.$display_name.'</span></li>';
  }
?>
  </ul>

<?php /* include_partial('__amazon_wishlist') */ ?>

 </div>
</div><!-- /row -->

<?php slot('inline_styles') ?>
/* highlight my <style> */
#support-koohii .padded-box-inset { border:1px solid #ebe5d8; border-bottom:2px solid #e0dace; }
#support-koohii .block { display:block; padding:5px; font-weight:bold; text-decoration:none; color:#1191dc; }
#support-koohii a strong { color:#30ab58; }

#patreon-patrons {
  margin-top:-0.5em; padding:1em 0 0; list-style:none; border-top:1px solid #e7e1d3;
}
#patreon-patrons li {
  list-style:none; display:inline-block; width:33.33%; text-align:center;
  font-size:16px; line-height:1.5em;
}

.become-a-patron { margin-left:3em;color:#e6461a; }

 /* bootstrap-xs-sm */
@media (max-width: 991px) {
  #patreon-patrons li { width:50%; font-size:14px; }
  .become-a-patron { display:block; margin:0.3em 0 0; }
}
<?php end_slot() ?>
