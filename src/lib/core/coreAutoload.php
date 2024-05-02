<?php
/**
 * Old autoload code from Core framework, to use the Peer classes that are
 * based on coreDatabase.
 *
 */

class coreAutoload
{
  static protected $instance = null;

  protected function __construct()
  {
  }

  static public function getInstance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new coreAutoload();
    }
    return self::$instance;
  }

  static public function register()
  {
    if (!spl_autoload_register([self::getInstance(), 'autoload']))
    {
      throw new Exception(sprintf('Unable to register %s::autoload as an autoloading method.', get_class(self::getInstance())));
    }
  }

  static public function unregister()
  {
    spl_autoload_unregister([self::getInstance(), 'autoload']);
  }

  /**
   * Autoloading of classes/models
   * 
   * Autoload attempts to load the file in this order :
   * 
   * <xyz>Peer            =>  load a database table model from lib/model/<xyz>.php
   *
   * @param  string    A class name.
   * @return boolean   Returns true if the class has been loaded
   */
  public function autoload($class)
  {
    $matches = [];

    if (preg_match('/^(\\w+)Peer$/', $class, $matches))
    {
      // simplifying tant que pas besoin de plus
      //$dirs = array();
      //$dirs[] = sfConfig::get('app_lib_dir').'/model';
      //$dirs[] = sfConfig::get('lib_dir').'/model';

      $fileName = $class.'.php';
      //foreach ($dirs as $dir)
      //{
$dir = sfConfig::get('sf_lib_dir').'/peer';
//        if (is_readable($dir.'/'.$fileName))
//        {
if (require_once($dir.'/'.$fileName)) {
          call_user_func([$class, 'getInstance']);

          return true;
}
//        }
      //}
    }
  }
}

