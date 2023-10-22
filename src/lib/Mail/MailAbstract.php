<?php

namespace Koohii\Mail;

abstract class MailAbstract
{
  abstract public function setBodyText($body);

  abstract public function setFrom($email, $name = '');

  abstract public function addTo($email, $name = '');

  abstract public function setSubject($subject);

  abstract public function send();
}
