<?php
  use_helper('Bootstrap', 'Form', 'Validation', 'Widgets', 'Decorator');
?>

<?php decorate_start('SideTabs', array('active' => 'editaccount')) ?>

  <h2>Edit Account</h2>

<?php
    // echo form_errors();

    echo form_tag('account/edit', array('class'=>'block'));

    echo _bs_form_group(
      '<label>Username</label>'
    . '<p class="form-control-static">'.$sf_user->getUserName().'</p>'
    );

    echo _bs_form_group(
      ['validate' => 'email'],
      _bs_input_email('email', [
        'label'    => 'Email',
        'helptext' => 'Please use a valid e-mail address so you can retrieve your password! We do NOT share your email.'
      ])
    );

    echo _bs_form_group(
      ['validate' => 'location'],
      _bs_input_text('location', [
        'label'     => 'Where do you live?',
        'optional'  => true,
        'helptext'  => 'Eg. "Tokyo, Japan" or just "Japan"'
      ])
    );

    echo _bs_form_group(
      ['validate' => 'timezone'],
      '<label for="form[timezone]">Timezone</label>'
    . select_tag('timezone', options_for_select(rtkTimezones::$timezones, $sf_request->getParameter('timezone')), array('class' => 'form-control'))
    . '<span class="help-block">'
    . 'Due flashcards appear each day at midnight local time. Adjust the timezone'
    . ' to your local time, or move it forward/backward if you\'d like for due cards'
    . ' to appear at another time of the day.'
    . '</span>'
    );

    echo _bs_form_group(
      _bs_submit_tag('Save changes')
    );
?>
  </form>

<?php decorate_end() ?>
