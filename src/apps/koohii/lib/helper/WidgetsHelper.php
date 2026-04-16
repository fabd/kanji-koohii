<?php
/**
 * Helpers to include user interface elements in the application templates.
 *
 *    ui_filter_std()
 *    ui_select_pager()
 *    ui_select_table()
 *    ui_data_table()
 *    ui_ibtn()
 *    ui_chart_vs()
 *    ui_progress_bar()
 *
 * @author   Fabrice Denis
 */

/**
 * Render a widget template with the given variables.
 *
 * @param array<string, mixed> $vars
 */
function _widgets_render(string $template, array $vars): string
{
  extract($vars, EXTR_REFS);
  ob_start();

  require dirname(__FILE__).'/templates/'.$template.'.php';

  return ob_get_clean();
}

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
 * @param string $label   Label, pass empty string to display switches without label
 * @param array  $links   Array of link definitions (array(name, internal_uri, options))
 * @param array  $options Options for the main div tag (class uiFilterStd will always be addded)
 *                        Also, the 'active' option (see above)
 *
 * @return string HTML representation
 */
function ui_filter_std(string $label, array $links, array $options = []): string
{
  // always set the widget class name in the main div tag
  $options['class'] = merge_html_classes($options['class'] ?? [], 'uiFilterStd');

  // add the JSFilterStd class to each link, and the 'active' class to the active item
  $active = isset($options['active']) ? (int) $options['active'] : false;

  for ($i = 0; $i < count($links); $i++) {
    $linkOptions = $links[$i][2] ?? [];

    $addClasses = 'JSFilterStd';

    if ($active === $i) {
      $addClasses .= ' active';
      unset($options['active']);
    }

    $linkClasses = merge_html_classes($linkOptions['class'] ?? [], $addClasses);

    $links[$i][2] = array_merge($linkOptions, ['class' => $linkClasses]);
  }

  return _widgets_render('ui_filter_std', ['links' => $links, 'label' => $label, 'options' => $options]);
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
 * @param uiSelectPager|false $pager uiSelectPager object or false
 * @param string              $slot  Slot name, leave out to use the default
 *
 * @return string HTML representation
 */
function ui_select_pager(uiSelectPager|false $pager = false, string $slot = 'widgets.ui.pager'): string
{
  if ($pager !== false) {
    slot($slot);

    echo _widgets_render('ui_select_pager', ['pager' => $pager]);

    end_slot();
  }

  return get_slot($slot);
}

/**
 * Return HTML for a uiSelectTable component.
 *
 * Optionally, returns the HTML for a uiSelectPager at the top and bottom of the table.
 *
 * @param uiSelectPager $pager        Optional pager, to display paging links and rows-per-page
 * @param array         $html_options Optional attributes for the <table> tag (same as sf's tag helpers)
 *
 * @return string HTML representation
 */
function ui_select_table(uiSelectTable $table, ?uiSelectPager $pager = null, array $html_options = []): string
{
  ob_start();

  if (!is_null($pager)) {
    echo ui_select_pager($pager);
  }

  $html_options['class'] = merge_html_classes('uiTabular', $html_options['class'] ?? []);

  echo _widgets_render('ui_select_table', ['table' => $table, 'table_options' => $html_options]);

  if (!is_null($pager)) {
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
 * @param object $table        an object with the getTableHead() and getTableBody() methods
 * @param array  $html_options
 *
 * @return string
 */
function ui_data_table(object $table, array $html_options = []): string
{
  if (!method_exists($table, 'getTableHead') || !method_exists($table, 'getTableBody')) {
    throw new sfException(__METHOD__.' Bad interface on $table');
  }

  ob_start();

  $html_options['class'] = merge_html_classes('uiTabular', $html_options['class'] ?? []);

  echo _widgets_render('ui_select_table', ['table' => $table, 'table_options' => $html_options]);

  return ob_get_clean();
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
 * @param string $name         Button text can contain HTML (eg. <span>), will NOT be escaped
 * @param string $internal_uri See link_to()
 * @param array  $options      See link_to()
 *
 * @return string
 */
function ui_ibtn(string $name, string $internal_uri = '', array $options = []): string
{
  $button_type = 'uiIBtnDefault';

  if (isset($options['type'])) {
    $button_type = $options['type'];
    unset($options['type']);
  }

  $options['class'] = merge_html_classes($options['class'] ?? [], ['uiIBtn', $button_type]);

  if (isset($options['icon'])) {
    $name = '<em class="icon icon-'.$options['icon'].'">'.$name.'</em>';
    unset($options['icon']);
  }

  $name = '<span>'.$name.'</span>';

  if ($internal_uri == '') {
    // $options['anchor'] = '';
    // $options['absolute'] = true;
  }

  return link_to($name, $internal_uri, $options);
}

/**
 * uiChartVs.
 *
 * Options:
 *   labelLeft, labelRight   Labels on each side
 *   valueLeft, valueRight   Values, will be summed up to calculate percentage
 *   labelLeftMax            Label to use when value of the other side is 0 (OPTIONAL)
 *   labelRightMax
 *
 * @see /doc/slicing/RevTK/charts/uiChartVs.html
 */
function ui_chart_vs(array $options)
{
  $valueTotal = $options['valueLeft'] + $options['valueRight'];
  $pctLeft    = ceil($options['valueLeft'] * 100 / $valueTotal);
  $pctRight   = 100 - $pctLeft;

  $captionLeft  = isset($options['labelLeftMax'])  && $options['valueRight'] == 0 ? $options['labelLeftMax'] : $options['labelLeft'];
  $captionRight = isset($options['labelRightMax']) && $options['valueLeft']  == 0 ? $options['labelRightMax'] : $options['labelRight'];

  $options = array_merge($options, [
    'pctLeft'      => $pctLeft,
    'pctRight'     => $pctRight,
    'bZeroLeft'    => $pctLeft  == 0,
    'bZeroRight'   => $pctRight == 0,
    'captionLeft'  => $captionLeft,
    'captionRight' => $captionRight,
  ]);

  return _widgets_render('ui_chart_vs', $options);
}

/**
 * ko-StripedProgressBar.
 *
 * Generate markup for a progress bar.
 *
 * Bars is an array of 'bar' definitions as associative arrays:
 *
 *   value   => value for this bar, between 0 and maxValue
 *   label   => optional label to output within SPAN and as title attribute on the SPAN, defaults to "min/max"
 *   class   => a class name for this SPAN, defaults to "g" (green), specify if using multiple bars
 *
 *    => <span class="r" title="optional label" style="width:15%;">optional label</span>
 *
 * Does not support minValue for now, so bars must be defined in order of size from largest to smallest,
 * the smallest will show on top of others.
 *
 * Options:
 * - optional attributes, as for the tag helpers
 * - "borderColor" with a proper css color value ("red" or "#f00") to override the default gray
 *   border from the stylesheet.
 *
 * @param array $bars     Associative array definitions for bars
 * @param int   $maxValue The max value corresponds to 100% of the bar width, related to each bar's value
 * @param array $options
 *
 * @return string HTML markup
 */
function ui_progress_bar(array $bars, int $maxValue, array $options = []): string
{
  // border color for the bar, override border-color from the stylesheet

  $innerDivOptions = [];
  if (isset($options['borderColor'])) {
    // override background color on outer div
    $options['style'] = "border-color:{$options['borderColor']};";
    // override border-color on inner div
    // $innerDivOptions['style'] = "border-color:{$options['borderColor']};";
    unset($options['borderColor']);
  }

  // merge widget class name
  $options['class'] = merge_html_classes($options['class'] ?? [], ['ko-StripedProgressBar']);

  // generate the bars as SPANs
  $spans = [];
  foreach ($bars as $bar) {
    if (!ctype_digit((string) $bar['value'])) {
      throw new sfException('ui_progress_bar()  "value" must be numeric');
    }

    if ($bar['value'] >= 0) {
      $percent     = $bar['value'] > 0 ? ceil($bar['value'] / $maxValue * 100) : 0;
      $label       = $bar['label'] ?? "{$bar['value']}/{$maxValue}";
      $spanOptions = [
        'class' => $bar['class'] ?? 'g',
        'title' => $label,
        'style' => "width:{$percent}%;",
      ];
      array_push($spans, content_tag('span', $label, $spanOptions));
    }
  }

  // span for the gloss overlay
  $spans[] = '<span class="x"></span>';

  $content = content_tag('div', implode('', $spans), $innerDivOptions);

  // generate the outer div
  return content_tag('div', $content, $options);
}
