<?php

abstract class MailAbstract
{
  abstract public function setBodyText($body);

  abstract public function setFrom($address, $name = '');

  abstract public function addTo($email, $name = '');

  abstract public function addReplyTo($email, $name = '');

  abstract public function setSubject($subject);

  abstract public function send();
}
