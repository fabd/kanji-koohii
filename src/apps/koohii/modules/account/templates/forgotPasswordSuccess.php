<?php
  use_helper('Form', 'Validation');
  $sf_request->setParameter('_homeFooter', true);
?>

    <h2>Forgot your password?</h2>

    <p>  Enter your email address below and you will receive new password instructions.</p>
    
    <p style="color:#822"><strong>If you do not receive an email please check the SPAM folder!</strong></p>

<div class="padded-box rounded mb-3">

    <?php echo form_errors() ?>
   
    <?php echo form_tag('@forgot_password', ['class'=>'']) ?>

      <div class="form-group">
        <label for="email_address" class="">Email address</label>
        <input type="text" name="email_address" class="form-control" id="email_address" placeholder="Email" style="">
      </div>

      <div class="form-group">
        <?php echo submit_tag('Send password instructions', ['class' => 'btn btn-success']) ?>
      </div>

    </form>

</div><!-- /panel -->


<?php koohii_onload_slot() ?>
  App.focusOnLoad('#email_address');
<?php end_slot() ?>

