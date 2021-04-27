<?php
/**
 * A much simpler version of symfony's view templating mainly for helpers that
 * render small widgets like buttons, pagers, etc.
 * 
 * A view collects variables and then renders them according to implementation.
 * 
 * @todo    Decide what to do with automatic escaping. Currently manual if not using Helpers.
 * 
 * @author  Fabrice Denis
 * 
 */

class coreView
{
  /**
   * Skip view execution.
   */
  const NONE = 'None';
  
  /**
   * Show an error view.
   */
  const ERROR = 'Error';

  /**
   * Show a success view.
   */
  const SUCCESS = 'Success';

  protected
    $context            = null,
    $decoratorFile      = false,
    $parameterHolder    = null,
      $moduleName         = '',
      $actionName         = '',
    $viewName           = '',
      $directory          = null,
      $template           = null,
      $extension          = '.php';
  
  const RENDER_HTML     = 1;

  /**
   * 
   * @param coreContext $context
   * @param string      $moduleName
   * @param string      $actionName
   * @param string      $viewName
   */
  public function __construct($context, $moduleName = '', $actionName = '', $viewName = '')
  {
    $this->context = $context;

    if (func_num_args() > 1) {
      $this->configure($moduleName, $actionName, $viewName);
    }

    $this->parameterHolder = new sfParameterHolder();
  }

  /**
   * Get the parameter holder of this view.
   *
   * @return sfParameterHolder The attribute parameter holder
   */
  public function getParameterHolder()
  {
    return $this->parameterHolder;
  }
    
  /**
   * Configure this view's template file based on module and action.
   * 
   * Action templates :
   * 
   *   :apps_dir:/modules/:moduleName:/views/:actionName:[ViewName]View.php
   *   
   * If the module is 'global', the template comes from the /apps/templates/ folder,
   * without 'View' appended.
   * 
   * If the viewName is different from coreView::SUCCESS, it will be added to the filename.
   *
   * @param string A module name
   * @param string An action name
   * @param string A view name
   *
   * @return string  Filename
   */
  protected function configure($moduleName, $actionName, $viewName)
  {
    if ($viewName===self::SUCCESS) {
      $viewName = '';
    }

      $this->moduleName = $moduleName;
      $this->actionName = $actionName;
      $this->viewName   = $viewName;

    if ($moduleName==='global')
    {
      $this->directory = coreConfig::get('sf_app_template_dir');
      $this->template = $actionName.'View'.$this->getExtension();
    }
    else
    {
      $this->directory = coreConfig::get('sf_app_module_dir').'/'.$moduleName.'/templates';
      $this->template = $actionName.$viewName.'View'.$this->getExtension();
    }

    if (!is_readable($this->directory.'/'.$this->template))
    {
      throw new Exception(sprintf("View file missing for module %s action %s", $moduleName, $actionName));
    }
  }

  /**
   * Set a template file explicitly.
   * 
   * If the template path is relative, it will be based on the currently
     * executing module's template sub-directory.
     * 
     * This function does not set the default template suffix or extension!
   * 
   * @param string $template  An absolute or relative filesystem path to a template
   */
  public function setTemplate($template)
  {
    if (sfToolkit::isPathAbsolute($template))
    {
      $this->directory = dirname($template);
      $this->template  = basename($template);
    }
    else
    {
      $this->directory = coreConfig::get('sf_app_module_dir').'/'.$this->context->getModuleName().'/templates';
      $this->template = $template;
    }
    
    if (!is_readable($this->directory.'/'.$this->template))
    {
      throw new Exception(sprintf("Template file missing %s", $template));
    }
  }

  /**
   * 
   * @return 
   */
  public function getDirectory()
  {
    return $this->directory;
  }

  /**
   * 
   * @return 
   */
  public function getTemplate()
  {
    return $this->template;
  }

  /**
   * Activate a decorator template file for this view.
   * 
   * @param mixed  A template name or false to disable the layout
   * @return
   */
  public function setDecoratorTemplate($templateName)
  {
    if (false === $templateName)
      {
        $this->decoratorFile = false;
          return;
      }
      else if (is_null($templateName))
      {
          return;
      }
    $this->decoratorFile = coreConfig::get('sf_app_template_dir').'/'.$templateName.'View'.$this->getExtension();
  }

  /**
   * Indicates whether this view has a decorator template set or not.
   * 
   * @return boolean True if this view has a decorator 
   */
  public function hasDecorator()
  {
    return ($this->decoratorFile!==false);
  }

  /**
   * Render the decorator file and fill in the content.
   * 
   * @return 
   * @param object $content
   */
  protected function decorate($content)
    {
      // set the decorator content as an attribute
      $this->parameterHolder->set('core_content', $content);
  
      // render the decorator template and return the result
      return $this->renderFile($this->decoratorFile);
  }

  /**
   * Render view as HTML or JSON depending on render mode.
   * 
   * @return 
   */
  public function render()
  {
    // skip view rendering ?
    if ($this->viewName===self::NONE)
    {
      return false;
    }

      // render the view contents
        $content = $this->renderFile($this->getDirectory().'/'.$this->getTemplate());

      // now render decorator template, if one exists
      if ($this->hasDecorator())
      {
          $content = $this->decorate($content);
      }
    
    return $content;
  }

  /**
   * Render template file using PHP as the templating engine.
   * 
   * @param  string Filename
   * @return string A string representing the rendered presentation
   */
  protected function renderFile($templateFile)
  {
    // load core and standard helpers
    //$helpers = array_unique(array_merge(array('Core', 'Url', 'Asset', 'Tag'/*, 'Escaping'*/), coreConfig::get('standard_helpers')));
    //coreToolkit::loadHelpers($helpers);

    extract($this->parameterHolder->getAll(), EXTR_REFS);

    // template shortcuts
    $_context  = sfContext::getInstance();
    $_request  = $_context->getRequest();
    $_params   = $_request->getParameterHolder();
    $_user     = $_context->getUser();
    $_response = $_context->getResponse();

    // render
    ob_start();
    ob_implicit_flush(false);
      
    require($templateFile);

    return ob_get_clean();
  }

  /**
   * Retrieves the current view extension.
   *
   * @return string The extension for current view.
   */
  public function getExtension()
  {
    return $this->extension;
  }
}
