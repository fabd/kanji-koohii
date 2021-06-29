<?php
  use_helper('FrontEnd', 'Widgets');
  $num_stories = StoriesPeer::getStoriesCounts($sf_user->getUserId());
?>

<h2>My Stories</h2>

<div class="mystories-stats text-xl mb-6">
  <strong><?php echo $num_stories->private ?></strong> private</li>, 
  <strong><?php echo $num_stories->public ?></strong> public</li>
  (<?php echo $num_stories->total ?> total)
</div>

<div class="mb-6 relative">
  <div class="absolute right-0 top-0">
    <?php echo _bs_button_with_icon('Export to CSV', 'study/export', ['icon' => 'fa-file']) ?>
  </div>

  <div id="MyStoriesSelect" class="mb-3"><!-- vue --></div>
</div>

<div id="MyStoriesComponent">
  <?php include_component('study', 'MyStoriesTable', ['stories_uid' => $sf_user->getUserId(), 'profile_page' => false]) ?>
</div>

<?php
  kk_globals_put('MYSTORIES_SORT_ACTIVE', $sort_active);
  kk_globals_put('MYSTORIES_SORT_OPTIONS', $sort_options);
?>
