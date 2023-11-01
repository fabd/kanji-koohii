<?php
use_helper('Form', 'Markdown', 'Validation');
$sf_request->setParameter('_homeFooter', true);
?>

  <h2>Contact</h2>
    
  <div class="markdown">
<?php markdown_begin(); ?>
Hi, I'm Fabrice!

Feedback is always appreciated, and can help motivate further developments. Thank you!

Please note if you like, you can also:

* Post **bugs and suggestions** directly to [fabd/kanji-koohii](https://github.com/fabd/kanji-koohii/issues) on Github

<?php if ($sf_user->isAuthenticated()): ?>
* Use your own email client, send mail to <a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;&#107;&#97;&#110;&#106;&#105;&#46;&#107;&#111;&#111;&#104;&#105;&#105;&#64;&#103;&#109;&#97;&#105;&#108;&#46;&#99;&#111;&#109;">&#107;&#97;&#110;&#106;&#105;&#32;&#8226;&#32;&#107;&#111;&#111;&#104;&#105;&#105;&#32;&#65312;&#32;&#103;&#109;&#97;&#105;&#108;&#32;&#8226;&#32;&#99;&#111;&#109;</a>
<?php endif; ?>

<?= markdown_end(); ?>
  </div>

<?php if ($sf_user->isAuthenticated()): ?>

  <p style="color:#484"><em><span class="required-legend">*</span> Please provide a valid email address, it will only be used to reply to your message.</em></p>

  <div class="ko-Box mb-3 max-w-[600px]">

<?php

  echo form_errors();

  echo form_tag('home/contact', ['class' => 'block']);

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
      'rows' => 8,
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
      The <strong>contact form</strong> is available to members only (please <?= link_to('sign in', '@login'); ?>).
    </p>

    <div class="ko-Box mb-3 max-w-[600px]">

        <p class="mb-2">
          <strong>If you are unable to sign in</strong>, contact:<br>
          <br>
          kanji &bull; koohii &#65312; gmail &bull; com
        </p>
    </div>

<?php endif; ?>

