<?php
slot('ManageSideNav', $active);

/**
 * Helper to set active class on the active list item.
 *
 * @param mixed $id
 * @param mixed $text
 * @param mixed $internal_uri
 */
function manageSideNav($id, $text, $internal_uri)
{
  $options = get_slot('ManageSideNav') === $id ? ['class' => 'active'] : [];

  return tag('li', $options, true).link_to($text, $internal_uri).'</li>';
}
?>
    <div class="side-menu">
      <h2>Add Cards</h2>
      <ul>
        <?= manageSideNav('addorder', _CJ('Remembering the Kanji'), 'manage/index'); ?></li>
        <?= manageSideNav('addcustom', 'Custom selection', 'manage/addcustom'); ?></li>
      </ul>
    </div>

    <div class="side-menu">
      <h2>Remove Cards</h2>
      <ul>
        <?= manageSideNav('removelist', 'Select from list', 'manage/removelist'); ?></li>
        <?= manageSideNav('removecustom', 'Custom selection', 'manage/removecustom'); ?></li>
      </ul>
    </div>

    <div class="side-menu">
      <h2>Edit Keywords</h2>
      <ul>
        <?= manageSideNav('editkeywords', 'Edit Keywords', 'manage/editkeywords'); ?></li>
        <?= manageSideNav('importkeywords', 'Import Keywords', 'manage/importKeywords'); ?></li>
      </ul>
    </div>
      
    <div class="side-menu">
      <h2>Export</h2>
      <ul>
        <?= manageSideNav('exportflashcards', 'Export flashcards', 'manage/export'); ?></li>
      </ul>
    </div>
