<?php
  use_helper('Bootstrap', 'Form', 'Validation');
  $sf_request->setParameter('_homeFooter', true);
?>

    <h2>Sign in</h2>

  <div class="padded-box-inset mb-8" style="max-width:380px">

<?php
    echo form_errors();

    echo form_tag('home/login', ['class'=>'']);

    echo input_hidden_tag('referer', $sf_request->getParameter('referer'));

    echo _bs_form_group(
      _bs_input_text('username', ['label' => 'Username', 'style' => 'max-width:300px'])
    );

    echo _bs_form_group(
      _bs_input_password('password', ['label' => 'Password', 'style' => 'max-width:300px'])
    );

    echo _bs_form_group(
      _bs_input_checkbox('rememberme', ['label' => 'Remember me'])
    );

    /*
    <label>
      <?php echo checkbox_tag('rememberme', '1', false, array('id' => 'rememberme', 'class' => '')) ?> Remember me
    </label>
    */
   
    echo _bs_form_group(
      _bs_submit_tag('Sign In', ['class' => 'btn-lg'])
    );
?>
   
    </form>

</div>

    <p><?php echo link_to('Forgot your password ?','@forgot_password') ?></p>



<?php koohii_onload_slot() ?>
App.ready(function() {
  var elFocus = YAHOO.util.Dom.get('username');
  if (elFocus)
  {
    elFocus.focus();
  }
});
<?php end_slot() ?>
