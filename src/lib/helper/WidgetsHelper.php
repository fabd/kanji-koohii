<?php
/**
 * Helpers to include user interface elements in the application templates.
 * 
 * Uses stylesheet /css/ui/widgets.css
 * 
 * @package  Helpers
 * @author   Fabrice Denis
 */

/**
 * Returns HTML for a uiFilterStd widget.
 * 
 * The links is an array of link definitions, in the format of the link_to() arguments:
 *   array(
 *     array($name, $internal_uri, $options),
 *     array($name, $internal_uri, $options),
 *     //...
 *   )
 *   
 * Options can be used to add attributes to the main div tag.
 * 
 * Add option 'active' as an integer to specify the active item: 0=first, 1=second, etc.
 * 
 * @param  string  $label     Label, pass empty string to display switches without label
 * @param  array   $links     Array of link definitions (array(name, internal_uri, options))
 * @param  array   $options   Options for the main div tag (class uiFilterStd will always be addded) 
 *                            Also, the 'active' option (see above)
 * 
 * @return string  HTML representation
 */
function ui_filter_std($label, $links, $options = [])
{
  // always set the widget class name in the main div tag
  $options['class'] = merge_html_classes($options['class'] ?? [], 'uiFilterStd');

  // add the JSFilterStd class to each link, and the 'active' class to the active item
  $active = isset($options['active']) ? (int)$options['active'] : false;

  for ($i = 0; $i < count($links); $i++)
  {
    $linkOptions = isset($links[$i][2]) ? $links[$i][2] : [];

    $addClasses = 'JSFilterStd';

    if ($active === $i)
    {
      $addClasses .= ' active';
      unset($options['active']);
    }

    $linkClasses = merge_html_classes($linkOptions['class'] ?? [], $addClasses);

    $links[$i][2] = array_merge($linkOptions, ['class' => $linkClasses]);
  }

  $view = new coreView(sfContext::getInstance());
  $view->getParameterHolder()->add(['links' => $links, 'label' => $label, 'options' => $options]);
  $view->setTemplate(dirname(__FILE__).'/templates/ui_filter_std.php');
  return $view->render();
}


/**
 * Set a slot and returns the HTML for a uiSelectPager.
 * 
 * The slot allows to re-print the pager at the top and bottom of a table by
 * running the pager template only once.
 * 
 * Examples:
 * 
 *  echo ui_select_pager($pager)
 *    => Set and print the pager slot with the default slot name
 *  echo ui_select_pager()
 *    => Print the HTML for previously set slot
 *  echo ui_select_pager($pager, 'pager2')
 *    => Print and set a pager with a custom slot name
 *       (allows different pagers on one template)
 *  echo ui_select_pager(false, 'pager2')
 *    => Print previously set pager with custom slot name
 * 
 * @param  mixed   $pager   uiSelectPager object or false
 * @param  string  $slot    Slot name, leave out to use the default
 * 
 * @return string  HTML representation
 */
function ui_select_pager($pager = false, $slot = 'widgets.ui.pager')
{
  if ($pager !== false)
  {
    slot($slot);
  
    $view = new coreView(sfContext::getInstance());
    $view->getParameterHolder()->add(['pager' => $pager]);
    $view->setTemplate(dirname(__FILE__).'/templates/ui_select_pager.php');
    echo $view->render();
  
    end_slot();
  }

  return get_slot($slot);
}


/**
 * Return HTML for a uiSelectTable component.
 * 
 * Optionally, returns the HTML for a uiSelectPager at the top and bottom of the table.
 * 
 * @param  uiSelectTable $table
 * @param  uiSelectPager $pager   Optional pager, to display paging links and rows-per-page
 * @param array $html_options   Optional attributes for the <table> tag (same as sf's tag helpers)
 * 
 * @return string  HTML representation
 */
function ui_select_table(uiSelectTable $table, uiSelectPager $pager = null, $html_options = [])
{
  ob_start();

  if (!is_null($pager))
  {
    echo ui_select_pager($pager);
  }

  $html_options['class'] = merge_html_classes('uiTabular', $html_options['class'] ?? []);

  $view = new coreView(sfContext::getInstance());
  $view->getParameterHolder()->add(['table' => $table, 'table_options' => $html_options]);
  $view->setTemplate(dirname(__FILE__).'/templates/ui_select_table.php');
  echo $view->render();
  
  if (!is_null($pager))
  {
    echo ui_select_pager();
  }

  return ob_get_clean();
}


/**
 * Return HTML for a data table.
 *
 * Uses the same helper template as the uiSelectTable component, to limit the damage
 * (ideally the select_table should be refactored to use a datasource interface...)
 * 
 * @param  mixed $table    An object with the getTableHead() and getTableBody() methods.
 *
 * @return string
 */
function ui_data_table($table)
{
  if (!method_exists($table, 'getTableHead') || !method_exists($table, 'getTableBody')) {
    throw new sfException(__METHOD__.' Bad interface on $table');
  }

  ob_start();

  $view = new coreView(sfContext::getInstance());
  $view->getParameterHolder()->add(['table' => $table]);
  $view->setTemplate(dirname(__FILE__).'/templates/ui_select_table.php');
  echo $view->render();
  
  return ob_get_clean();
}

/**
 * Helper to set the display property inline stlye in html templates.
 * 
 * Example:
 *   <div ... style="<3php echo ui_display($active===3) 3>">
 * 
 * Echoes the display property with ending ";"
 */
function ui_display($bDisplay)
{
  echo $bDisplay ? 'display:block;' : 'display:none;';
}

/**
 * Returns a uiIBtn element.
 * 
 * The parameters are the same as for UrlHelper link_to().
 * The difference is an additional "type" option, and an empty uri will default to '#'.
 *
 * Example markup:
 *
 *  <code>
 *   <a href="#" class="uiIBtn uiIBtnDefault"><span><em class="icon icon-edit">Edit</em></span></a>
 *  </code>
 * 
 * Additional options:
 * 
 *  'type'     The type of button, defaults to "uiIBtnDefault". This sets the main class
 *             of the uiIBtn element.
 *  'icon'     Adds an EM element inside the SPAN, with classname "icon icon-XYZ" where XYZ
 *             is the given icon name. 
 * 
 * Examples:
 *  
 *   echo ui_ibtn('Go');
 *   echo ui_ibtn('Disabled', '#', array('type' => 'uiIBtnDisabled'));
 *   echo ui_ibtn('Custom class', '#', array('class' => 'JsAction-something'));
 *   echo ui_ibtn('Google', 'http://www.google.com' );
 *   echo ui_ibtn('Click me!', '#', array('onclick' => 'alert("Hello world!");return false;') );
 * 
 * @param  string  $name          Button text can contain HTML (eg. <span>), will NOT be escaped
 * @param  string  $internal_uri  See link_to()
 * @param  array   $options       See link_to()
 * @return string
 */
function ui_ibtn($name, $internal_uri = '', $options = [])
{
  $button_type = 'uiIBtnDefault';
  
  if (isset($options['type']))
  {
    $button_type = $options['type'];
    unset($options['type']);
  }

  $options['class'] = merge_html_classes($options['class'] ?? [], ['uiIBtn', $button_type]);

  if (isset($options['icon']))
  {
    $name = '<em class="icon icon-'.$options['icon'].'">'.$name.'</em>';
    unset($options['icon']);
  }

  $name = '<span>'.$name.'</span>';    

  if ($internal_uri == '') {
    //$options['anchor'] = '';
    //$options['absolute'] = true;
  }

  return link_to($name, $internal_uri, $options);
}

