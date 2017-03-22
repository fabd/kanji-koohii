<?php
/**
 * Sends Koohii mails.
 * 
 * Requires configuration in app.yml
 * 
 *   Each configuration value is an associative array with 'email' and 'name' properties.
 *    
 *   all:
 *     .dummy:
 *       
 *       # from (email, name) for automatic mailings (registration, password change, ...)
 *       email_robot:       { email: '...', name: 'Kanji Koohii' }
 *       
 *       # to   (email, name) for contact page form
 *       email_feedback_to: { email: '...',  name: 'Fabrice' }
 *    
 */

class rtkMail extends MailAbstract
{
  /**
   * Sends Forgot Password email with new password.
   * 
   */
  public function sendForgotPasswordConfirmation($userAddress, $userName, $rawPassword)
  {
    $from = sfConfig::get('app_email_robot');
    $this->setFrom($from['email'], isset($from['name']) ? $from['name'] : '');

    $this->addTo($userAddress, $userName);
    $this->setSubject('Your new password at '._CJ('Kanji Koohii!'));
    $this->setPriority(1);
    $body = $this->renderTemplate('forgotPasswordConfirmation', array('username' => $userName, 'password' => $rawPassword));
    $this->setBodyText($body);
    $this->send();
  }

  /**
   * Sends email to new members to confirm account details.
   * 
   */
  public function sendNewAccountConfirmation($userAddress, $userName, $rawPassword)
  {
    $from = sfConfig::get('app_email_robot');
    $this->setFrom($from['email'], isset($from['name']) ? $from['name'] : '');

    $this->addTo($userAddress, $userName);
    $this->setSubject('Welcome to '._CJ('Kanji Koohii!'));
    $this->setPriority(1);
    $body = $this->renderTemplate('newAccountConfirmation', array('username' => $userName));
    $this->setBodyText($body);
    $this->send();
  }
  
  /**
   * Send a feedback email to the webmaster.
   * 
   * @param  string  $subject     Email subject
   * @param  string  $name_from   From address (reply to)
   * @param  string  $username    Author (username)
   * @param  string  $message     The message
   */
  public function sendFeedbackMessage($subject, $name_from, $author, $message)
  {
    $message = trim(strip_tags($message));

    $this->setFrom($name_from, $author);

    $to = sfConfig::get('app_email_feedback_to');
    $this->addTo($to['email'], isset($to['name']) ? $to['name'] : '');

    $this->setSubject($subject);
    $this->setBodyText($message);
    $this->send();
  }

  /**
   * Sends email to confirm the new login details after a password update.
   * 
   */
  public function sendUpdatePasswordConfirmation($userAddress, $userName, $rawPassword)
  {
    $from = sfConfig::get('app_email_robot');
    $this->setFrom($from['email'], isset($from['name']) ? $from['name'] : '');

    $this->addTo($userAddress, $userName);
    $this->setSubject('Account update at '._CJ('Kanji Koohii!'));

    $body = $this->renderTemplate('updatedPasswordConfirmation', array(
      'username' => $userName, 'password' => $rawPassword, 'email' => $userAddress));
    $this->setBodyText($body);
    $this->send();
  }
}
