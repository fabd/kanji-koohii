<?php use_helper('Form', 'Validation') ?>

<?php //DBG::request(); ?>

    <h2>Edit Flashcard (Advanced)</h2>

    <p>This page lets you edit a flashcard meta data. Please be SUPER CAREFUL. There is no undo.</p>


<?php if ($status === 'success'): ?>
<div class="confirmwhatwasdone rounded-css3" style="margin:0 0 1em;">
  Flashcard succesfully updated / created.<br/>
  <em>( 'created_on' zero date is normal, it means a flashcard created before Jan 2015, which did not have this field)</em>
  <pre style="margin:0;">
    <?php print_r($cardInfo); ?>
  </pre>
</div>
<?php endif ?>

<?php echo form_tag('manage/advanced', ['class' => 'block']) ?>

  <?php echo form_errors() ?>

<ul>
  <li>
    <span class="lbl"><?php echo label_for('f_kanji', 'Kanji (character)') ?></span>
    <span class="fld medium"><?php echo input_tag('f_kanji', '', ['class' => '']) ?></span>
  </li>
  <li>
    <span class="lbl"><?php echo label_for('f_leitnerbox', 'Leitner Box') ?></span>
    <span class="fld medium"><?php echo input_tag('f_leitnerbox', '2', ['class' => '']) ?> (1 = fail/new, 2 = one review, ...)</span>
  </li>
  <li>
    <span class="lbl"><?php echo label_for('f_expiredays', 'Due date') ?></span>
    <span class="fld medium"><?php echo input_tag('f_expiredays', '0', ['class' => '']) ?> (in DAYS from now, 0 = due)</span>
  </li>
  <li>
    <span class="lbl"><?php echo label_for('f_failurecount', 'Fail count') ?></span>
    <span class="fld medium"><?php echo input_tag('f_failurecount', '0', ['class' => '']) ?></span>
  </li>
  <li>
    <span class="lbl"><?php echo label_for('f_successcount', 'Sucess count') ?></span>
    <span class="fld medium"><?php echo input_tag('f_successcount', '1', ['class' => '']) ?></span>
  </li>

  <li><span class="lbl"></span>
    <span class="btn"><?php echo submit_tag('Update/Create') ?></span>
  </li>
</ul>

</form>

  <p> <em style="color:#800">If the flashcard does not exist it is created.</em></p>

  <p> <strong>Failed cards</strong> : use BOX 1,  DUE days 0</p>

  <p> <strong>NEW cards</strong> : <em style="color:#800">do NOT use this page</em>. Use <?php echo link_to('Add Custom selection', 'manage/addcustom') ?> instead.</p>

  <p> <strong>Total reviews</strong> = fail count + success count.</p>

  <p> <strong>Due date</strong> : days from now, positive number (eg. 1 = due tomorrow)</p>

  <p> <strong>Last review</strong> : will be time of update (now)</p>





