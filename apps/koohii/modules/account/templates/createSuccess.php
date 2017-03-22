<?php
  use_helper('Form', 'Validation');
  $sf_request->setParameter('_homeFooter', true);
?>

<h2>Register an account</h2>

<p>
  Your email is NOT shared with any other service.<br/>
  <em>Please use a valid email so you can retrieve your password if you forget it!</em> 
</p>

<?php //DBG::request() ?>

<div class="padded-box-inset mb-1" style="max-width:380px">

<?php 
    echo form_errors();
      
    echo form_tag('account/create', array('class'=>'', 'autocomplete' => 'false'));

    echo _bs_form_group(
      _bs_input_text('username', array('label' => 'Username'))
    );

    echo _bs_form_group(
      _bs_input_email('email', array('label' => 'Email', 'autocomplete' => 'email'))
    );

    echo _bs_form_group(
      _bs_input_password('password', array('label' => 'Password', 'autocomplete' => 'current-password'))
    );

    echo _bs_form_group(
      _bs_input_password('password2', array('label' => 'Confirm password', 'autocomplete' => 'new-password'))
    );

    echo _bs_form_group(
      _bs_input_text('location', array(
        'label'     => 'Where do you live?',
        'optional'  => true,
        'helptext'  => 'Eg. "Tokyo, Japan" or just "Japan"',
        'autocomplete' => 'off'
      ))
    );

    echo _bs_form_group(
      array('class' => 'form-section' /*, 'style' => 'background:#eeeaab;'*/),

      _bs_input_text('question', array(
        'label'     => 'What is the capital of Japan? <span style="font-weight:normal">(main city)</span>',
        'helptext'  => '(Help us stop spam!)',
        'autocomplete' => 'off'
      ))
    );
?>
    <p>If you are stuck you can <?php echo link_to('request an account', '@contact') ?></p>
<?php
    echo _bs_form_group(
      _bs_submit_tag('Create Account')
    );

    /*
    <div class="form-group">
      <?php echo label_for('username', 'Username', array('class' => 'control-label')) ?>
      <?php echo input_tag('username', '', array('class' => 'form-control', 'id' => 'username')) ?>
    </div>

    <div class="form-group">
      <?php echo label_for('email', 'Email') ?>
      <?php echo input_tag('email', '', array('class' => 'form-control', 'id' => 'email')) ?>
    </div>

    <div class="form-group">
      <?php echo label_for('password', 'Password') ?>
      <?php echo input_password_tag('password', '', array('class' => 'form-control', 'id' => 'password')) ?>
    </div>

    <div class="form-group">
      <?php echo label_for('password2', 'Confirm password') ?>
      <?php echo input_password_tag('password2', '', array('class' => 'form-control', 'id' => 'password2')) ?>
    </div>

    <div class="form-group">
      <?php echo label_for('location', 'Where do you live?') ?><span class="form-optional">(optional)</span>
      <?php echo input_tag('location', '', array('class' => 'form-control', 'id' => 'location')) ?>
      <span class="legend"></span>
    </div>

    <div class="form-group form-section" style="background:#eeeaab;">
      <label for="question">Help us fight spam!</label><br/>
<span style="font-style:italic; <?php echo $sf_request->hasError('question') ? ' color:red;':''; ?>">
What is the capital of Japan?
</span>
        <?php echo input_tag('question', '', array('class' => 'form-control', 'id' => 'question')) ?>

    </div>

    <p>If you are stuck you can <?php echo link_to('request an account', '@contact') ?></p>

    <div class="form-group">
      <?php echo submit_tag('Create Account', array('class' => 'btn btn-success')) ?>
    </div>
    */
?>
    </form>

</div><!-- /panel -->


<?php koohii_onload_slot() ?>
App.ready(function() {
  var elFocus = YAHOO.util.Dom.get('username');
  if (elFocus)
  {
    elFocus.focus();
  }
});
<?php end_slot() ?>
