<?php use_helper('Form', 'Validation', 'Widgets', 'Decorator') ?>
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
        'long'  => 'Volume 1 : 5th edition or earlier.<br/>Volume 3 : 1st or 2nd edition.<br/>Newly Approved General-Use Kanji (3008-3030)',
        'count' => '3030'
      ],
      [
        'short' => 'New Edition',
        'long'  => 'Volume 1 : 6th edition.<br/>Volume 3 : 3rd edition.',
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
  <table class="blocky">
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

  <p>The selected edition affects the Heisig index numbers displayed in the Study pages and on the flashcards. It also changes
    the order in which characters are presented in the Study pages, and which <?php echo _CJ('kanji') ?> will be added to your
    flashcards when you enter Heisig indexes in the Manage pages.</p>

  <p><span style="color:#484;">Switching between editions is safe</span>. Stories and flashcards are always linked to a unique
    character, regardless of what index is selected here. Any flashcards currently in your deck will remain as they are, and will
      continue to be scheduled in the SRS.
    </p>

  <p><strong>Old Edition ONLY</strong> : includes 23 characters from <a href="http://nirc.nanzan-u.ac.jp/en/files/2012/12/RK1-Supplement.pdf" target="blank" class="link-pdf">Newly Approved General-Use Kanji (pdf)</a>.
  These are added at the end, frame numbers 3008 - 3030.</p>

  <span class="btn"><?php echo submit_tag('Save Changes') ?></span>

  </form>

<?php decorate_end() ?>
