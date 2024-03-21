<?php
  use_helper('Form', 'Links', 'Markdown', 'Validation', 'Widgets', 'Decorator');
?>
<?php slot('inline_styles') ?>
td label { white-space:nowrap; }
td input { vertical-align:middle; }
<?php end_slot() ?>
<?php 
  // FIXME  Attention!! Must match sequences info in rtkIndex.php
  if (CJ_HANZI) {
    $eds = [
      [
        'short' => 'Traditional Hanzi',
        'long'  => 'Volume 1 (1500 characters) +<br/>Volume 2 (1535 characters).',
        'count' => '3035'
      ],
      [
        'short' => 'Simplified Hanzi',
        'long'  => 'Volume 1 (1500 characters) +<br/>Volume 2 (1518 characters).',
        'count' => '3018'
      ]
    ];
  }
  else {
    $eds = [
      [
        'short' => 'Old Edition',
        'long'  => <<<END
Volume 1 : 5th edition or earlier <samp class="ml-2 text-success-darker">#1-2042</samp><br/>
Volume 3 : 1st or 2nd edition <samp class="ml-2 text-success-darker">#2043-3007</samp><br/>
Newly Approved General-Use Kanji <samp class="ml-2 text-success-darker">#3008-3030</samp>
END,
        'count' => '3030'
      ],
      [
        'short' => 'New Edition',
        'long'  => <<<END
Volume 1 : 6th edition <samp class="ml-2 text-success-darker">#1-2200</samp><br/>
Volume 3 : 3rd edition <samp class="ml-2 text-success-darker">#2201-3000</samp>
END,
        'count' => '3000'
      ]
    ];
  }
?>

<?php decorate_start('SideTabs', ['active' => 'sequence']) ?>

  <h2>Select <?php echo ucfirst(_CJ('kanji')) ?> Sequence</h2>

  <?php echo form_errors() ?>
  <?php echo form_tag('account/sequence') ?>

  <p>Select the <strong><?php echo _CJ('Remembering the Kanji') ?></strong> book editions you are studying with.</p>

  <?php $sequences = rtkIndex::getSequences(); ?>
  <table class="blocky mb-8">
    <thead>
      <tr><th>Edition</th><th>Description</th><th>Count</th></tr>
    </thead>
    <tbody>
    <?php for ($i = 0; $i < count($eds); $i++): ?>
      <tr>
        <td><label><?php echo radiobutton_tag('optSeq[]', $sequences[$i]['classId'], false) ?> <strong><?php echo $eds[$i]['short'] ?></strong></label></td>
        <td><?php echo $eds[$i]['long'] ?></td>
        <td><?php echo $eds[$i]['count'] ?></td>
      </tr>
    <?php endfor ?>
    </tbody>
  </table>

<?php markdown_begin() ?>
The selected edition affects the Heisig index numbers displayed in the Study pages and on the flashcards. It also changes the order in which characters are presented in the Study pages, and which kanji will be added to your flashcards when you enter Heisig indexes in the Manage pages.

<span class="text-success-dark">**Switching between editions is safe**</span>.
Stories and flashcards are always linked to a unique character, regardless of what index is selected here. Any flashcards currently in your deck will remain as they are, and will continue to be scheduled in the SRS.

**Keywords are always from the last edition!** This ensures everyone shares stories based on the same keywords. In particular please note with the 6th Edition errata Dr. Heisig swapped the keywords for "town" (now  町) and "village" (now 村).

**Old Edition ONLY** : includes 23 characters from <?= link_to_rk1_supplement() ?> (Newly Approved General-Use Kanji).
These are added at the end, frame numbers 3008 - 3030.
<?php echo markdown_end() ?>

<?php
    echo _bs_form_group(
      _bs_submit_tag('Save changes')
    );
?>

  </form>

<?php decorate_end() ?>
