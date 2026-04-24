<?php
use_stylesheet('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Lora:ital,wght@0,400;0,600;1,400&display=swap');

$activeCount     = PatreonMembersPeer::countActivePatrons();
$activePatrons   = PatreonMembersPeer::getActivePublicPatrons();
$anonActiveCount = $activeCount - count($activePatrons);
$formerPatrons   = PatreonMembersPeer::getFormerPublicPatrons();
$anonFormerCount = PatreonMembersPeer::countFormerAnonymous();
?>
<div class="ko-PatronsList mt-16">

  <!-- Header -->
  <header class="text-center mb-16">
    <h2 class="text-4xl font-semibold mb-4 italic">Our Patrons</h2>
    <p class="text-lg opacity-80 max-w-lg mx-auto">Thank you for your continued support!</p>
  </header>

  <!-- Active Patrons Section -->
  <section class="mb-20">
    <div class="flex items-center gap-4 mb-6">
      <h3 class="">
        Active Support <span class="opacity-70 font-medium">(<?= $activeCount ?> patrons)</span>
      </h3>
      <div class="flex-grow h-px ko-PatronsList-hairline border-t"></div>
    </div>

    <div class="rounded-2xl overflow-hidden border ko-PatronsList-hairline shadow-sm">
      <!-- Header Row -->
      <div class="ko-PatronsList-tableHead px-6 py-3 flex justify-between text-xs font-bold uppercase tracking-wider opacity-60">
        <span>Name</span>
        <span>Supporting Since</span>
      </div>

      <!-- Patron Rows -->
      <div class="">
        <?php foreach ($activePatrons as $patron): ?>
        <div class="ko-PatronsList-row flex justify-between items-center transition-colors hover:bg-white/50">
          <span class="font-medium"><?= $patron['full_name'] ?></span>
          <span class="text-sm font-mono opacity-70"><?= date('M Y', strtotime($patron['pledge_start'])) ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Anonymous Footer -->
      <?php if ($anonActiveCount > 0): ?>
      <div class="ko-PatronsList-footer px-6 py-6 text-center italic opacity-60 text-md bg-white/20">
        And <strong><?= $anonActiveCount ?></strong> anonymous patrons
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Past Patrons Section -->
  <section>
    <div class="flex items-center gap-4 mb-8">
      <h3 class="">
        Past Patrons
      </h3>
      <div class="flex-grow h-px ko-PatronsList-hairline border-t"></div>
    </div>

    <div class="ko-PatronsList-past space-y-1 mb-4">
      <?php foreach ($formerPatrons as $patron): ?>
      <div><?= $patron['full_name'] ?></div>
      <?php endforeach; ?>
    </div>

    <?php if ($anonFormerCount > 0): ?>
    <div class="italic text-sm opacity-50">+ <?= $anonFormerCount ?> anonymous</div>
    <?php endif; ?>

  </section>

</div><!-- /ko-PatronsList -->
