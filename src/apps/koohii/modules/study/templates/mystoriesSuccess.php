<?php
  use_helper('FrontEnd', 'Widgets');
  $num_stories = StoriesPeer::getStoriesCounts($sf_user->getUserId());
?>

<h2 class="ux-text-2xl">My Stories</h2>

<?php if ($num_stories->total === 0): ?>
  <p>
    This page will let you browse all the kanji stories you have edited in the <?= link_to('Study page', 'study/index'); ?>.
  </p>
<?php else: ?>

  <div class="ux-text-xl mb-6">
    <strong><?= $num_stories->private; ?></strong> private</li>, 
    <strong><?= $num_stories->public; ?></strong> public</li>
    (<?= $num_stories->total; ?> total)
  </div>

  <div class="mb-6 relative">
    <div class="absolute right-0 top-0">
      <?= _bs_button_to(
          'Export to CSV<i class="fa fa-arrow-down ml-2"></i>',
          'study/export',
          [
            'class' => 'ko-Btn ko-Btn--success',
          ]
          );
      ?>
    </div>

    <div id="MyStoriesSelect" class="mb-3"><!-- vue --></div>
  </div>

  <div id="MyStoriesComponent">
    <?php include_component('study', 'MyStoriesTable', [
      'stories_uid' => $sf_user->getUserId(),
      'profile_page' => false,
    ]); ?>
  </div>
<?php endif; ?>

<?php
  kk_globals_put([
    'MYSTORIES_SORT_ACTIVE' => $sort_active,
    'MYSTORIES_SORT_OPTIONS' => $sort_options
  ]);
?>
