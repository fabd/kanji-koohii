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


  <img class="support-logo" style="width:126px" src="https://s3.amazonaws.com/patreon_public_assets/toolbox/patreon.png"><br>

  <div class="padded-box no-gutter-xs-sm mb-8">
    <p>Patreon is a great way to support this website on an ongoing basis.</p>

    <a class="btn btn-lg btn-patreon" href="https://www.patreon.com/kanjikoohii" target="_blank">
      Become a Patron (recurring pledge)
    </a>
  </div>

  <img class="support-logo" style="width:126px" src="/images/3.0/support/paypal.png"><br>

  <div id="support-paypal" class="padded-box no-gutter-xs-sm mb-8">
    <p>PayPal is also a great way to support my work. (Note: if you chose the <em>recurring</em> option, you can cancel it at anytime from your PayPal account).</p>

<?php if (null === sfConfig::get('app_fork')) { include_partial('_paypalDonateButton'); } ?>

  </div>
 

  <h3>Patrons (past &amp; present)</h3>

  <p class="mb-2"><strong>Thank you for supporting Kanji Koohii!</strong></p>

  <?php $patrons = PatreonPeer::getPatronsList(); ?>
  <div class="padded-box no-gutter-xs-sm">
  <ul id="patreon-patrons">
  <?php //DBG::printr($patrons); ?>
<?php
  $sf_user_name = $sf_user->getUserName();

  foreach ($patrons as $pa)
  {
    $style        = $pa['username'] == $sf_user_name ? ['class' => 'is-patron'] : [];
    $display_name = !empty($pa['username']) ?
      link_to_member($pa['username'], $style) :
      escape_once($pa['pa_full_name']);
    echo '<li><span class="whitespace-nowrap">'.$display_name.'</span></li>';
  }
?>
  </ul>
  </div>

<?php /* include_partial('__amazon_wishlist') */ ?>

 </div>
</div><!-- /row -->

