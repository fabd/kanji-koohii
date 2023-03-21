<?php
/**
 * Sends Koohii mails.
 *
 * CONFIGURATION
 *
 *   Requires configuration in app.yml
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
 *
 * RENDERING EMAIL TEMPLATES
 *
 *   Email templates are stored in `%sf_app_template_dir%/emails` by default.
 *
 *   For example:
 *
 *     $body = renderTemplate('newAccountConfirmation', ['username' => 'JohnDoe456'])
 *     $this->setBodyText($body);
 */

// uncomment this one to revert to php mail
// use Koohii\Mail\MailAbstract;

use Koohii\Mail\MailAbstractSMTP as MailAbstract;

class rtkMail extends MailAbstract
{
  private string $templateDir;

  public function __construct()
  {
    $this->setTemplateDir(sfConfig::get('sf_app_template_dir').'/emails');
  }

  /**
   * Sends Forgot Password email with new password.
   *
   * @param string $userAddress
   * @param string $userName
   * @param string $rawPassword
   */
  public function sendForgotPasswordConfirmation($userAddress, $userName, $rawPassword)
  {
    $from = sfConfig::get('app_email_robot');
    $this->setFrom($from['email'], $from['name'] ?? '');

    $this->addTo($userAddress, $userName);
    $this->setSubject('Your new password at '._CJ('Kanji Koohii!'));
    $body = $this->renderTemplate('forgotPasswordConfirmation', [
      'username' => $userName,
      'password' => $rawPassword,
    ]);
    $this->setBodyText($body);

    return $this->send();
  }

  /**
   * Sends email to new members to confirm account details.
   *
   * @param string $userAddress
   * @param string $userName
   * @param string $rawPassword
   */
  public function sendNewAccountConfirmation($userAddress, $userName, $rawPassword)
  {
    $from = sfConfig::get('app_email_robot');
    $this->setFrom($from['email'], $from['name'] ?? '');

    $this->addTo($userAddress, $userName);
    $this->setSubject('Welcome to '._CJ('Kanji Koohii!'));
    $body = $this->renderTemplate('newAccountConfirmation', [
      'username' => $userName,
    ]);
    $this->setBodyText($body);

    return $this->send();
  }

  /**
   * Send a feedback email to the webmaster.
   *
   * @param string $subject   Email subject
   * @param string $name_from From address (reply to)
   * @param string $username  Author (username)
   * @param string $message   The message
   * @param string $author
   */
  public function sendFeedbackMessage($subject, $name_from, $author, $message)
  {
    $message = trim(strip_tags($message));

    $this->setFrom($name_from, $author);

    $to = sfConfig::get('app_email_feedback_to');
    $this->addTo($to['email'], $to['name'] ?? '');

    $this->setSubject($subject);
    $this->setBodyText($message);

    return $this->send();
  }

  /**
   * Sends email to confirm the new login details after a password update.
   *
   * @param string $userAddress
   * @param string $userName
   * @param string $rawPassword
   */
  public function sendUpdatePasswordConfirmation($userAddress, $userName, $rawPassword)
  {
    $from = sfConfig::get('app_email_robot');
    $this->setFrom($from['email'], $from['name'] ?? '');

    $this->addTo($userAddress, $userName);
    $this->setSubject('Account update at '._CJ('Kanji Koohii!'));

    $body = $this->renderTemplate('updatedPasswordConfirmation', [
      'username' => $userName,
      'password' => $rawPassword,
      'email' => $userAddress,
    ]);
    $this->setBodyText($body);

    return $this->send();
  }

  /**
   * Sets the dreictory where email templates are stored.
   *
   * @param string $path template directory, no trailing slash
   */
  private function setTemplateDir($path)
  {
    $this->templateDir = $path;
  }

  /**
   * Simple templating for rendering email contents.
   *
   * @param string $templateName
   * @param array  $templateVars (optional)
   */
  private function renderTemplate($templateName, $templateVars = [])
  {
    $templateFile = $this->templateDir.'/'.$templateName.'.php';

    if (!is_readable($templateFile))
    {
      throw new sfException('Email template file not found <b>'.$templateFile.'</b>');
    }

    // load core and standard helpers
    sfProjectConfiguration::getActive()->loadHelpers(['Tag', 'Url']);

    extract($templateVars, EXTR_REFS);

    // render
    ob_start();
    ob_implicit_flush(false);

    require $templateFile;

    return ob_get_clean();
  }
}
