<?php
  use_helper('Links');
  $sf_request->setParameter('_homeFooter', true);
?>
<div class="row">
  <div class="col-lg-9 mx-auto">

    <h2>Support Kanji Koohii<span class="visible-md-lg"> Development</span></h2>

    <p>
      Maintaining and developing new features takes a considerable amount of time. Your support is very important and could allow me to free up more time for development. <em>Thank you!</em>
    </p>

    <img class="block mb-8" style="width:126px" src="https://s3.amazonaws.com/patreon_public_assets/toolbox/patreon.png" />

    <div class="ko-Box no-gutter-xs-sm mb-8">
    
    <p>Patreon is a great way to support this website on an ongoing basis.</p>

    <a class="ko-Btn ko-Btn--large ko-Btn--patreon" href="https://www.patreon.com/kanjikoohii" target="_blank">
      Become a Patron (recurring pledge)
    </a>
    </div>

    <img class="block mb-8" style="width:126px" src="/images/3.0/support/paypal.png" />

    <div class="ko-Box no-gutter-xs-sm mb-8">
      <p>
        PayPal is also a great way to support my work. (Note: if you chose the <em>recurring</em> option, you can cancel it at anytime from your PayPal account).
      </p>
      <div class="kk-PaypalForm">
<?php if (null === sfConfig::get('app_fork'))
{
  include_partial('_paypalDonateButton');
}
?>
      </div>
    </div>
 
    <h3>Patrons (past &amp; present)</h3>

    <p class="mb-2"><strong>Thank you for supporting Kanji Koohii!</strong></p>

    <?php $patrons = PatreonPeer::getPatronsList(); ?>
  
    <div class="ko-Box no-gutter-xs-sm">
      <ul class="kk-PatronsList">
<?php //DBG::printr($patrons);?>
<?php
  $sf_user_name = $sf_user->getUserName();

  foreach ($patrons as $pa)
  {
    $style = $pa['username'] == $sf_user_name ? ['class' => 'is-patron'] : [];
    $display_name = !empty($pa['username']) ?
      link_to_member($pa['username'], $style) :
      escape_once($pa['pa_full_name']);
    echo <<<HTML
      <li class="kk-PatronsList-item">
        <span class="whitespace-nowrap">{$display_name}</span>
      </li>
      HTML;
  }
?>
      </ul>
    </div>

<?php /* include_partial('__amazon_wishlist') */ ?>

  </div>
</div><!-- /row -->

