<?php
use_helper('Links');
with_footer();
// (fabd): double-check autoloading on a simple page
// $declaredClasses = get_declared_classes();
// LOG::info(count($declaredClasses).' declared classes');
// LOG::info($declaredClasses);
?>
<div class="row">
  <div class="col-lg-9 mx-auto">

    <h2>Support Kanji Koohii Development</h2>

    <p class="mb-8">
      Maintaining and developing new features takes a considerable amount of time. Your support is very important and could allow me to free up more time for development. <em>Thank you!</em>
    </p>

    <div class="flex gap-4 items-stretch mb-8">
      <div class="w-1/2">
        <img class="block mb-4" style="width:126px" src="/images/3.0/support/patreon.png" />
        <div class="ko-Box min-h-[135px] ux-text-md">
      
          <p>Patreon is a great way to support this website on an ongoing basis.</p>
          <a class="ko-Btn ko-Btn--large ko-Btn--patreon" href="https://www.patreon.com/kanjikoohii" target="_blank">
            Become a Patron
          </a>
        </div>
      </div>
      <div class="w-1/2">
        <img class="block mb-4" style="width:126px" src="/images/3.0/support/paypal.png" />

        <div class="ko-Box min-h-[135px] ux-text-md">
          <p>
            PayPal is also a great way to support my work.
          </p>
          <div class="ko-PaypalForm">
    <?php if (!KK_ENV_FORK) {
      include_partial('_paypalDonateButton');
    } ?>
          </div>
        </div>
      </div>
    </div>
 
<?php include_partial('PatronsList'); ?>  

  </div>
</div><!-- /row -->

