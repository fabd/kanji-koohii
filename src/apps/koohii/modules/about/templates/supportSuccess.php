<?php
  use_helper('Links');
  $sf_request->setParameter('_homeFooter', true);

  // (fabd): double-check autoloading on a simple page
  // $declaredClasses = get_declared_classes();
  // LOG::info(count($declaredClasses).' declared classes');
  // LOG::info($declaredClasses);
?>
<div class="row">
  <div class="col-lg-9 mx-auto">

    <h2>Support Kanji Koohii<span class="visible-md-lg"> Development</span></h2>

    <p class="mb-8">
      Maintaining and developing new features takes a considerable amount of time. Your support is very important and could allow me to free up more time for development. <em>Thank you!</em>
    </p>

    <img class="block mb-4" style="width:126px" src="/images/3.0/support/patreon.png" />

    <div class="ko-Box no-gutter-xs-sm mb-8">
    
    <p>Patreon is a great way to support this website on an ongoing basis.</p>

    <a class="ko-Btn ko-Btn--large ko-Btn--patreon" href="https://www.patreon.com/kanjikoohii" target="_blank">
      Become a Patron
    </a>
    </div>

    <img class="block mb-4" style="width:126px" src="/images/3.0/support/paypal.png" />

    <div class="ko-Box no-gutter-xs-sm mb-8">
      <p>
        PayPal is also a great way to support my work. (Note: if you chose the <em>recurring</em> option, you can cancel it at anytime from your PayPal account).
      </p>
      <div class="ko-PaypalForm">
<?php if (!KK_ENV_FORK) { include_partial('_paypalDonateButton'); } ?>
      </div>
    </div>
 
    <h3>Patrons (past &amp; present)</h3>

    <p class="mb-2"><strong>Thank you for supporting Kanji Koohii!</strong></p>

    <?php $patrons = PatreonPeer::getPatronsList(); ?>
  
    <div class="ko-Box no-gutter-xs-sm">
      <ul class="ko-PatronsList">
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
      <li class="ko-PatronsList-item">
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

