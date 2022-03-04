<?php 
  use_helper('Form', 'Validation');
  $sf_request->setParameter('_homeFooter', true);
?>

    <h2>Contact</h2>
    
    <p>Hi, I'm Fabrice!</p>

    <p>Note: you can post <strong>bugs and suggestions</strong> to our <a href="https://github.com/fabd/kanji-koohii/issues">Github issues tracker</a>.</p>

<?php if ($sf_user->isAuthenticated()): ?>

    <p style="color:#484"><em><span class="required-legend">*</span> Please provide a valid email address, it will only be used to reply to your message.</em></p>

    <div class="padded-box rounded mb-3 max-w-[600px]">

<?php
    echo form_tag('home/contact', ['class'=>'block']);

    echo _bs_form_group(
      ['validate' => 'name'],
      _bs_input_email('name', [
        'label' => 'Name',
        'class' => 'JsFocusOnLoadInput max-w-[300px]',
      ])
    );

    echo _bs_form_group(
      ['validate' => 'email'],
      _bs_input_email('email', [
        'label' => 'Email *',
        'placeholder' => 'Email',
        'class' => 'max-w-[300px]',
      ])
    );

    echo _bs_form_group(
      ['validate' => 'message'],
      _bs_input_textarea('message', [
        'label' => 'Message',
        'rows' => 8
      ])
    );

    echo _bs_form_group(
      _bs_submit_tag('Send message')
    );
?>

    </form>
  </div>

<?php else: ?>

    <p style="color:#844">
      The <strong>contact form</strong> is available to members only (please <?= link_to('sign in', '@login') ?>).
    </p>

    <div class="padded-box rounded mb-3 max-w-[600px]">

        <p class="mb-2">
          <strong>If you are unable to sign in</strong>, contact:<br>
          <br>
          kanji &bull; koohii &#65312; gmail &bull; com
        </p>
    </div>

<?php endif ?>

