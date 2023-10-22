<?php
/**
 * NOTE!
 *   - obsolete code using Zend Mail - to be removed eventually.
 *   - if we want to revert to php mail() then extend this class in rtkMail.php.
 *
 * MailAbstract is an abstraction layer for sending email, using Zend_Mail.
 * Requires bridge to Zend classes and autoloading setup in Application Configuration
 */

namespace Koohii\Mail;

use MailAbstract;
use sfConfig;
use Zend_Mail;

class MailAbstractPHP extends MailAbstract
{
  /** @var Zend_Mail */
  protected $mailer;

  /** @var string */
  protected $templateDir;

  protected const CHARSET_UTF8 = 'utf-8';

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

    $this->mailer = new Zend_Mail(self::CHARSET_UTF8);
  }

  public function setBodyText($body)
  {
    $this->mailer->setBodyText($body, self::CHARSET_UTF8);
  }

  public function setFrom($address, $name = '')
  {
    $this->mailer->setFrom($address, $name);
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

    // Note: Zend_Mail causes Exception in case of error
    return true;
  }
}
