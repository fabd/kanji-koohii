<?php
/**
 * MailAbstract is an abstraction layer for sending email, using Zend_Mail.
 * Requires bridge to Zend classes and autoloading setup in Application Configuration.
 *
 * May eventually be replaced by providing a `MailAbstractSMTP` instead in rtkMail.php.
 */

namespace Koohii\Mail;

use sfConfig;
use Zend_Mail;

class MailAbstractPHP extends MailAbstract
{
  /** @var Zend_Mail */
  protected $mailer;

  /** @var string */
  protected $charset = 'utf-8';

  /**
   * Constructor, set mailer defaults and template directory
   * where email templates are taken from.
   */
  public function __construct()
  {
    // require Zend here only when we need it
    $zend_inc_dir = sfConfig::get('sf_lib_dir').'/vendor';
    set_include_path($zend_inc_dir.PATH_SEPARATOR.get_include_path());

    require_once $zend_inc_dir.'/Zend/Loader.php';
    spl_autoload_register(['Zend_Loader', 'autoload']);

    $this->mailer = new Zend_Mail($this->charset);
  }

  public function setBodyText($body)
  {
    $this->mailer->setBodyText($body, $this->charset);
  }

  public function setFrom($email, $name = '')
  {
    $this->mailer->setFrom($email, $name);
  }

  public function addTo($email, $name = '')
  {
    $this->mailer->addTo($email, $name);
  }

  public function setSubject($subject)
  {
    $this->mailer->setSubject($subject);
  }

  public function send()
  {
    $this->mailer->send();
  }
}
