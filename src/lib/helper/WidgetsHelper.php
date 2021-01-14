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
  $options['class'] = phpToolkit::merge_class_names(isset($options['class']) ? $options['class'] : [], ['uiFilterStd']);

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

    $linkClasses = phpToolkit::merge_class_names(isset($linkOptions['class']) ? $linkOptions['class'] : [], $addClasses);

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
 * 
 * @return string  HTML representation
 */
function ui_select_table(uiSelectTable $table, uiSelectPager $pager = null)
{
  ob_start();

  if (!is_null($pager))
  {
    echo ui_select_pager($pager);
  }

  $view = new coreView(sfContext::getInstance());
  $view->getParameterHolder()->add(['table' => $table]);
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
 * Return html structure (to echo) for tabs in the manner of "sliding doors".
 *
 * SPANs are used because they can be styled on the :hover state in IE6
 * ( a:hover span {...} ).
 * 
 * Structure:
 * 
 *   <div class="ui-tabs" id="custom-id">
 *     <ul>
 *       <li><a href="#"><span>Link text</span></a></li>
 *       ...
 *     </ul>
 *     <div class="clear"></div>
 *   </div>
 * 
 * 
 * The $links argument is declared like this:
 * 
 *   array(
 *     array($name, $internal_uri, $options),
 *     array($name, $internal_uri, $options),
 *     ...
 *   )
 *   
 * The tab definitions are identical to the link_to() helper:
 *   
 *   $name          Label for the tab
 *   $internal_uri  Internal uri, or absolute url, defaults to '#' if empty (optional)
 *   $options       Html attribute options (optional)
 *   
 * By default the first tab is set active (class "active" on the LI tag). Specify the
 * index of the tab to be active, or FALSE to not add an "active" class.
 * 
 * @see    http://www.alistapart.com/articles/slidingdoors/
 * 
 * @param  array   $links    An array of tab definitions (see above).
 * @param  mixed   $active   Index of the active tab, defaults to the first tab.
 *                           Use FALSE to explicitly set no active tab (or use your own class).
 * @param  array   $options  Options for the container DIV element. By default the class "ui-tabs"
 *                           is added. Add "uiTabs" class for defaults styles, id for the javascript component.
 * 
 * @return string  Html code
 */
function ui_tabs($tabs, $active = 0, $options = [])
{
  ob_start();

  // add the "ui-tabs" class name
  $options['class'] = phpToolkit::merge_class_names(isset($options['class']) ? $options['class'] : [], ['ui-tabs']);
  echo tag('div', $options, true) . "\n<ul>\n";

  $tab_index = 0;
  foreach ($tabs as $tab)
  {
    $name = '<span>'.$tab[0].'</span>';
    $internal_uri = isset($tab[1]) ? $tab[1] : '#';
    $options = isset($tab[2]) ? $tab[2] : [];

    $class_active = (is_int($active) && $active===$tab_index) ? ' class="active"' : '';
    echo '<li'.$class_active.'>'.link_to($name, $internal_uri, $options).'</li>'."\n";
    
    $tab_index++;
  }
  
  echo "</ul>\n<div class=\"clear\"></div>\n</div>\n";
  
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

  $options['class'] = phpToolkit::merge_class_names(isset($options['class']) ? $options['class'] : [], ['uiIBtn', $button_type]);

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

