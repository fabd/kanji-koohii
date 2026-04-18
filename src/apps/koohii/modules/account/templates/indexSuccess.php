<?php
use_helper('Date', 'SimpleDate', 'Widgets', 'Decorator');
with_footer();
?>
<?php decorate_start('SideTabs', ['active' => 'overview']); ?>

  <h2>My Account</h2>

  <h3>Statistics</h3>

  <section class="mb-8">
  <table class="blocky">
    <tr><th style="width:130px;">Flashcard Count</hd><td><?= $flashcard_count.' ('.$reviewed_count.' reviewed)';
?></td></tr>
    <tr><th>Total Reviews</th><td><?= $total_reviews; ?></td></tr>
  </table>
  </section>

  <h3>Profile</h3>

  <section class="mb-8">
  <table class="blocky">
    <tr><th style="width:130px;">Username</th>
        <td><b><?= esc_specialchars($user['username']); ?></b></td></tr>
    <tr><th>Email</th>
        <td><?= esc_specialchars($user['email']); ?>
        <div style="font:11px/1em Verdana, sans-serif;color:#484;font-style:italic;white-space:nowrap">(your email is not visible to anyone else)</div>
        </td></tr>
    <tr><th>Location</th>
        <td><?= esc_specialchars($user['location']); ?></td></tr>
    <tr><th>Timezone</th>
        <td><?= rtkTimezones::$timezones[(string) $user['timezone']]; ?></td></tr>
    <tr><th>Joined</th><td><?= date('j M Y', $user['ts_joindate']); ?></td></tr>
    <tr><th>Last Login</th><td><?= time_ago_in_words($user['ts_lastlogin'], true); ?> ago</td></tr>
  </table>
  </section>

<?php
if (!KK_ENV_FORK) {
  require_once sfConfig::get('sf_lib_dir').'/vendor/Patreon/__patreon.php';
  $patron_info = PatreonPeer::getPatronInfo($sf_user->getUserId());
  ?>

  <h3>Patreon</h3>
  <div class="ko-Box">
<?php if (false !== $patron_info): ?>
  <p>Thank you for supporting Kanji Koohii, <strong><?= $patron_info['pa_full_name']; ?></strong>!</p>

  <p>
    <strong>Patron status</strong>: ACTIVE.
  </p>

<?php else: ?>
  <p><strong>Support Kanji Koohii development</strong> and (soon) enjoy some perks!</p>
  <p><a href="https://www.patreon.com/kanjikoohii" style="color:#e6461a;font-size:120%;">Become a patron</a></p>
  <p>Already a patron? <?= kkPatreon::get_login_link('Link your account'); ?></p>
<?php endif; ?>
  </div>

<?php } ?>

<?php decorate_end(); ?>
