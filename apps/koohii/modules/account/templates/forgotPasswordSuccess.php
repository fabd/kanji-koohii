<?php
  use_helper('Form', 'Validation');
  $sf_request->setParameter('_homeFooter', true);
?>

    <h2>Forgot your password?</h2>

    <p>  Enter your email address below and you will receive new password instructions.</p>
    
    <p style="color:#822">If you do not receive an email
        <strong>check the spam folder</strong> of your email service!<br/><br/> Also double check that you typed in the email address
        correctly. </p>

<div class="padded-box-inset mb-1">

    <?php echo form_errors() ?>
   
    <?php echo form_tag('@forgot_password', array('class'=>'')) ?>

      <div class="form-group">
        <label for="email_address" class="">Email address</label>
        <input type="text" name="email_address" class="form-control" id="email_address" placeholder="Email" style="">
      </div>

      <div class="form-group">
        <?php echo submit_tag('Send password instructions', array('class' => 'btn btn-success')) ?>
      </div>

    </form>

</div><!-- /panel -->


<?php koohii_onload_slot() ?>
App.ready(function() {
  var elFocus = YAHOO.util.Dom.get('email_address');
  if (elFocus)
  {
    elFocus.focus();
  }
});
<?php end_slot() ?>

