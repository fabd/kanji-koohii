<?php
/**
 * Send email via SMTP with Google SMTP server.
 *
 * Pre-requisites:
 *   - setup the GMail account with 2FA
 *   - get an app password
 *   - add the credentials (email, password) to the CREDENTIALS_FILE
 *     (the file is excluded from repo cf. root .gitignore `__*`)
 */

namespace Koohii\Mail;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use sfException;

class MailAbstractSMTP extends MailAbstract
{
  public static string $PROTOCOL = self::PROTOCOL_SMTP;

  /** @var PHPMailer */
  private $mailer;

  // private credentials file, relative to current folder
  private const CREDENTIALS_FILE = '__gmail-smtp-credentials.php';

  public function __construct()
  {
    // true : throw exceptions
    $this->mailer = new PHPMailer(true);
  }

  public function setBodyText($body)
  {
    $this->mailer->isHTML(false);
    $this->mailer->Body = $body;
  }

  public function setFrom($email, $name = '')
  {
    $this->mailer->setFrom($email, $name);
    // dump('setFrom()', $email, $name);
  }

  public function addTo($email, $name = '')
  {
    $this->mailer->addAddress($email, $name);
    // dump('addTo()', $email, $name);
  }

  public function addReplyTo($email, $name = '')
  {
    $this->mailer->addReplyTo($email, $name);
  }

  public function setSubject($subject)
  {
    $this->mailer->Subject = $subject;
    // dump('setSubject()', $subject);
  }

  /**
   * @return bool true if PHPMailer send() is succesfull
   */
  public function send(): bool
  {
    $mail = $this->mailer;

    $credentials = $this->getCredentials();
    // dump('credentials', $credentials);

    $isMailSent = false;

    try {
      $this->configureSmtp($mail, $credentials);

      $mail->send();

      $isMailSent = true;
    } catch (Exception $e) {
      error_log('PHPMailer exception '.$mail->ErrorInfo);
    }

    return $isMailSent;
  }

  /**
   * @param array $credentials cf. getCredentials()
   */
  private function configureSmtp(PHPMailer $mail, array $credentials)
  {
    // Tell PHPMailer to use SMTP
    $mail->isSMTP();

    // Enable SMTP debugging
    // SMTP::DEBUG_OFF = off (for production use)
    // SMTP::DEBUG_CLIENT = client messages
    // SMTP::DEBUG_SERVER = client and server messages
    $mail->SMTPDebug = KK_ENV_DEV ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;

    // Set the hostname of the mail server
    $mail->Host = 'smtp.gmail.com';

    // Whether to use SMTP authentication
    $mail->SMTPAuth = true;

    // -------------------------------------------------------------
    // SMTP authentication method 1 : use GMail credentials
    // -------------------------------------------------------------
    // Set the SMTP port number:
    // - 465 for SMTP with implicit TLS, a.k.a. RFC8314 SMTPS or
    // - 587 for SMTP+STARTTLS
    $mail->Port = 465;
    // Set the encryption mechanism to use:
    // - SMTPS (implicit TLS on port 465) or
    // - STARTTLS (explicit TLS on port 587)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    // Username to use for SMTP authentication - use full email address for gmail
    $mail->Username = $credentials['username'];
    // Password to use for SMTP authentication
    $mail->Password = $credentials['password'];
    // -------------------------------------------------------------

    $mail->CharSet = PHPMailer::CHARSET_UTF8;
  }

  /**
   * Get credentials for GMail SMTP from a file which is excluded from the public repo.
   *
   * @return array Array with keys 'username', 'password'
   */
  private function getCredentials()
  {
    $credentialsPath = realpath(dirname(__FILE__)).'/'.self::CREDENTIALS_FILE;

    if (!file_exists($credentialsPath)) {
      throw new sfException('MailAbstractSTMP(): can not open SMTP credentials file. Guru Meditation #84289535');
    }

    $credentials = require $credentialsPath;
    assert(isset($credentials['username'], $credentials['password']));

    return $credentials;
  }
}
