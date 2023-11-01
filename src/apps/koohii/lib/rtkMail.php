<?php
/**
 * Sends Koohii emails, optionally using email templates.
 *
 *   sendNewAccountConfirmation($userAddress, $userName, $rawPassword)
 *   sendForgotPasswordConfirmation($userAddress, $userName, $rawPassword)
 *   sendUpdatePasswordConfirmation($userAddress, $userName, $rawPassword)
 *
 *   sendFeedbackMessage($subject, $from_name, $from_addr, $message)
 *
 *   parseAddress($address)
 *   formatAddress($from)
 *
 *   renderTemplate($templateName, $templateVars = [])
 *
 *
 * Example configuration in app.yml (see parseAddress() for accepted formats):
 *
 *   all:
 *     email_robot:       'Kanji Koohii <kanji.koohii+robot@domain.com>'
 *     email_feedback_to: 'Fabrice <fabrice@domain.com>'
 *
 *
 * Email templates are stored in `%sf_app_template_dir%/emails`.
 *
 *   For example:
 *
 *     $body = renderTemplate('newAccountConfirmation', ['username' => 'JohnDoe456'])
 *     $this->setBodyText($body);
 */

// uncomment this to use php mail()
// use Koohii\Mail\MailAbstractPHP  as MailAbstractAPI;

// uncomment this to use SMTP via GMail server
use Koohii\Mail\MailAbstractSMTP as MailAbstractAPI;

class rtkMail extends MailAbstractAPI
{
  /**
   * Sends Forgot Password email with new password.
   *
   * @param string $userAddress
   * @param string $userName
   * @param string $rawPassword
   */
  public function sendForgotPasswordConfirmation($userAddress, $userName, $rawPassword)
  {
    $from = self::parseAddress(sfConfig::get('app_email_robot'));
    $this->setFrom($from['email'], $from['name']);

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
    $from = self::parseAddress(sfConfig::get('app_email_robot'));
    $this->setFrom($from['email'], $from['name']);

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
   * @param string $subject
   * @param string $from_addr
   * @param string $from_name
   * @param string $message
   */
  public function sendFeedbackMessage($subject, $from_addr, $from_name, $message)
  {
    $message = trim(strip_tags($message));
    $this->setFrom($from_addr, $from_name);

    $to = self::parseAddress(sfConfig::get('app_email_feedback_to'));
    $this->addTo($to['email'], $to['name']);

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
    $from = self::parseAddress(sfConfig::get('app_email_robot'));
    $this->setFrom($from['email'], $from['name']);

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
   * Simple templating for rendering email contents.
   *
   * @param string $templateName
   * @param array  $templateVars (optional)
   */
  private function renderTemplate($templateName, $templateVars = [])
  {
    $templateDir = sfConfig::get('sf_app_template_dir').'/emails';
    $templateFile = $templateDir.'/'.$templateName.'.php';

    if (!is_readable($templateFile)) {
      throw new sfException("Template file not found: `{$templateFile}`");
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

  /**
   * Simple parsing of email address, no need for all the fancy RFC stuff.
   *
   * Example accepted formats:
   *
   *    admin@website.com
   *    The Admin <admin@website.com>
   *    "The Admin" <admin@website.com>
   *
   * @param string $address Full address as `"Name" <email>` or just `email`.
   *                        Quotes around the name are optional.
   *
   * @return array array with `name` and `email` keys, `name` is an empty string
   *               if it was not provided
   */
  public static function parseAddress(string $address)
  {
    $address = trim($address ?? '');
    assert(!empty($address));

    $name = '';
    $email = '';

    if (preg_match('/"?([^><,"]+)"?\s*((?:<[^><,]+>)?)/', $address, $matches)) {
      if (!empty($matches[2])) {
        $name = trim($matches[1]);
        $email = trim($matches[2], '<>');
      } else {
        $email = $matches[1];
      }
    }

    return ['name' => $name, 'email' => $email];
  }

  /**
   * Reverse of parseAddress(). Formats name and email to `"name" <email>` or just
   * `email`.
   *
   * @param array $from Array with keys `name` and `email`
   *
   * @return string
   */
  public static function formatAddress(array $from)
  {
    return !empty($from['name'])
      ? "\"{$from['name']}\" <{$from['email']}>"
      : $from['email'];
  }
}
