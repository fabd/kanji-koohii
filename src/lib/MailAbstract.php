<?php
/**
 * MailAbstract is an abstraction layer for sending email, using Zend_Mail.
 * Requires bridge to Zend classes and autoloading setup in Application Configuration
 * 
 * In addition to abstracting a mailer library, it offers a method for rendering
 * the email body as a php template with variables. Email templates are stored
 * in %sf_app_template_dir%/emails by default.
 * 
 * renderTemplate($templateName, $templateVars = array())
 * 
 * Note: if using another charset than the default, setCharset() must be called
 *       before setBodyText().
 *
 * @author  Fabrice Denis
 */
 
class MailAbstract
{
  protected
    $mailer         = null,
    $charset        = 'utf-8',
    $templateDir    = null;

  /**
   * Constructor, set mailer defaults and template directory
   * where email templates are taken from.
   * 
   * @return 
   */
  public function __construct()
  {
    // require Zend here only when we need it
    $zend_inc_dir = sfConfig::get('sf_lib_dir').'/vendor';
    set_include_path($zend_inc_dir.PATH_SEPARATOR.get_include_path());
    require_once($zend_inc_dir.'/Zend/Loader.php');
    spl_autoload_register(['Zend_Loader', 'autoload']);

    $this->setTemplateDir(sfConfig::get('sf_app_template_dir').'/emails');
    $this->mailer = new Zend_Mail($this->charset);
  }

  /**
   * Changes the default charset, applies to the next setBodyText() calls
   * 
   * @param string $charset  Character set (eg. 'utf-8')
   */
  public function setCharset($charset)
  {
    $this->charset = $charset;
  }

  public function setBodyText($body)
  {
    $this->mailer->setBodyText($body, $this->charset);
  }

  public function setFrom($address, $name = '')
  {
    $this->mailer->setFrom($address, $name);
  }

  public function addTo($email, $name='')
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

  public function setPriority($priority)
  {
    // there is no priority method in Zend_Mail?
  }

  /**
   * Sets the dreictory where email templates are stored.
   * 
   * @param string $path  Template directory, no trailing slash.
   */
  public function setTemplateDir($path)
  {
    $this->templateDir = $path;
  }

  /**
   * Simple templating for rendering email contents.
   * 
   * @return 
   * @param object $templateFile
   * @param object $templateVars[optional]
   */
  public function renderTemplate($templateName, $templateVars = [])
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
      
    require($templateFile);

    return ob_get_clean();
  }
}
