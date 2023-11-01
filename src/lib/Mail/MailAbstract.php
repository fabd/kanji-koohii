<?php

namespace Koohii\Mail;

abstract class MailAbstract
{
  // PHP   : builtin sendmail()
  // SMTP  : SMTP via GMail with 2fa
  public const PROTOCOL_PHP = 'PHP';
  public const PROTOCOL_SMTP = 'SMTP';

  // used for debugging, tell us if we are using PHP or SMTP
  public static string $PROTOCOL;

  abstract public function setBodyText($body);

  abstract public function setFrom($email, $name = '');

  abstract public function addTo($email, $name = '');

  abstract public function addReplyTo($email, $name = '');

  abstract public function setSubject($subject);

  abstract public function send(): bool;
}
