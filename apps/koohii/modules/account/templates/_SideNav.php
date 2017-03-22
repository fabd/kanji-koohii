<?php
slot('SideNavActive', $active);

// Helper to set active class on the active list item.
function output_sidenav_item($id, $text, $internal_uri)
{
  $options = get_slot('SideNavActive') === $id ? array('class' => 'active') : array();
  echo tag('li', $options, true) . link_to($text, $internal_uri) . '</li>';
}
?>
<div class="side-menu">
  <h2>Account</h2>
  <ul>
    <?php output_sidenav_item('overview', 'Overview', 'account/index') ?></li>
    <?php output_sidenav_item('editaccount', 'Edit Account', 'account/edit') ?></li>
    <?php output_sidenav_item('changepassword', 'Change Password', 'account/password') ?></li>
  </ul>
</div>

<div class="side-menu">
  <h2>Settings</h2>
  <ul>
    <?php output_sidenav_item('flashcards', 'Flashcards', 'account/flashcards') ?></li>
    <?php output_sidenav_item('sequence', 'RTK Edition', 'account/sequence') ?></li>
    <?php #output_sidenav_item('opt22', 'Study Options', 'account/studyoptions') ?></li>
  </ul>
</div>

<?php /* ?>
  <h2>Review</h2>
  <ul>
    <?php output_sidenav_item('opt31', 'Flashcard Options', 'account/flashcardoptions') ?></li>
  </ul>
*/ ?>
