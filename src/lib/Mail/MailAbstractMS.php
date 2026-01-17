<?php

/**
 * Send email via SMTP with MailerSend API.
 *
 * https://github.com/mailersend/mailersend-php#setup
 */

namespace Koohii\Mail;

use Exception;
use MailerSend\Exceptions\MailerSendException;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\MailerSend;
use sfException;

class MailAbstractMS extends MailAbstract
{
  public static string $PROTOCOL = self::PROTOCOL_SMTP;

  private const CREDENTIALS_FILE = '__mailersend-api-key.php';

  protected array $recipients = [];
  protected ?string $fromEmail = null;
  protected ?string $fromName = null;
  protected ?string $replyToEmail = null;
  protected ?string $replyToName = null;
  protected ?string $subject = null;
  protected ?string $text = null;
  protected ?string $html = null;

  public function __construct() {}

  public function setBodyText($body)
  {
    $this->text = $body;
  }

  public function setFrom($email, $name = '')
  {
    $this->fromEmail = $email;
    $this->fromName = $name;
  }

  public function addTo($email, $name = '')
  {
    $this->recipients[] = new Recipient($email, $name);
  }

  public function addReplyTo($email, $name = '')
  {
    $this->replyToEmail = $email;
    $this->replyToName = $name;
  }

  public function setSubject($subject)
  {
    $this->subject = $subject;
  }

  public function send(): bool
  {
    $credentials = $this->getCredentials();
    $mailersend = new MailerSend(['api_key' => $credentials['apikey']]);

    $params = (new EmailParams())
      ->setFrom($this->fromEmail)
      ->setFromName($this->fromName)
      ->setRecipients($this->recipients)
      ->setSubject($this->subject)
      ->setText($this->text)
    ;

    if ($this->replyToEmail) {
      $params->setReplyTo($this->replyToEmail);
      if ($this->replyToName) {
        $params->setReplyToName($this->replyToName);
      }
    }

    try {
      $mailersend->email->send($params);

      return true;
    } catch (MailerSendException $e) {
      error_log('MailerSend exception: '.$e->getMessage());

      return false;
    } catch (Exception $e) {
      error_log('General exception in MailAbstractMS: '.$e->getMessage());

      return false;
    }
  }

  /**
   * Get credentials from a file which is excluded from the public repo.
   *
   * @return array
   */
  private function getCredentials()
  {
    $credentialsPath = realpath(dirname(__FILE__)).'/'.self::CREDENTIALS_FILE;

    if (!file_exists($credentialsPath)) {
      throw new sfException('MailAbstractMS: can not open credentials file.');
    }

    $credentials = require $credentialsPath;
    assert(isset($credentials['apikey']));

    return $credentials;
  }
}
