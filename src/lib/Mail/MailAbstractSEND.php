<?php

/**
 * Send email via SMTP with SendGrid API.
 *
 * https://www.twilio.com/docs/sendgrid/for-developers/sending-email/quickstart-php
 */

namespace Koohii\Mail;

use Exception;
use SendGrid;
use SendGrid\Mail\Mail;
use sfException;

class MailAbstractSEND extends MailAbstract
{
  public static string $PROTOCOL = self::PROTOCOL_SMTP;

  /** @var Mail */
  private $mailer;

  private const CREDENTIALS_FILE = '__sendgrid-api-key.php';

  public function __construct()
  {
    $this->mailer = new Mail();
  }

  public function setBodyText($body)
  {
    $this->mailer->addContent('text/plain', $body);
  }

  public function setFrom($email, $name = '')
  {
    $this->mailer->setFrom($email, $name);
  }

  public function addTo($email, $name = '')
  {
    $this->mailer->addTo($email, $name);
  }

  public function addReplyTo($email, $name = '')
  {
    $this->mailer->setReplyTo($email, $name);
  }

  public function setSubject($subject)
  {
    $this->mailer->setSubject($subject);
  }

  public function send(): bool
  {
    $mail = $this->mailer;

    $credentials = $this->getCredentials();
    // dump('credentials', $credentials);

    $isMailSent = false;

    $sendgrid = new SendGrid($credentials['apikey']);

    try {
      $response = $sendgrid->send($mail);
      // printf("Response status: %d\n\n", $response->statusCode());

      // $headers = array_filter($response->headers());
      // echo "Response Headers\n\n";
      // foreach ($headers as $header) {
      //   echo '- '.$header."\n";
      // }

      $isMailSent = true;
    } catch (Exception $e) {
      error_log('SendGrid/Mail exception '.$e->getMessage()."\n");
    }

    return $isMailSent;
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
      throw new sfException('MailAbstractSEND(): can not open credentials file.');
    }

    $credentials = require $credentialsPath;
    assert(isset($credentials['apikey']));

    return $credentials;
  }
}
