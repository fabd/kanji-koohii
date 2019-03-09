<?php
/**
 * GadgetsHelper
 * 
 * GadgetsHelper is the application-specific widget helpers (not to be confused with
 * the Widgets helpers.
 *
 * Methods:
 *
 *  ui_chart_vs()         Generate markup for a uiChartVs bar chart
 *  ui_progress_bar()     Generate markup for a progress bar.
 * 
 * 
 * @author  Fabrice Denis
 */

/**
 * uiChartVs
 * 
 * Options:
 *   labelLeft, labelRight   Labels on each side
 *    valueLeft, valueRight   Values, will be summed up to calculate percentage
 *   labelLeftMax            Label to use when value of the other side is 0 (OPTIONAL)
 *   labelRightMax
 * 
 * @see /doc/slicing/RevTK/charts/uiChartVs.html
 */
function ui_chart_vs(array $options)
{
  $valueTotal = $options['valueLeft'] + $options['valueRight'];
  $pctLeft  = ceil($options['valueLeft'] * 100 / $valueTotal);
  $pctRight = 100 - $pctLeft;

  $captionLeft = isset($options['labelLeftMax']) && $options['valueRight']==0 ? $options['labelLeftMax'] : $options['labelLeft'];
  $captionRight= isset($options['labelRightMax']) && $options['valueLeft']==0 ? $options['labelRightMax'] : $options['labelRight'];

  $options = array_merge($options, array(
    'pctLeft'     => $pctLeft,
    'pctRight'    => $pctRight,
    'bZeroLeft'   => $pctLeft==0,
    'bZeroRight'  => $pctRight==0,
    'captionLeft' => $captionLeft,
    'captionRight'=> $captionRight
  ));

  $view = new coreView(sfContext::getInstance());
  $view->getParameterHolder()->add($options);
  $view->setTemplate(dirname(__FILE__).'/templates/ui_chart_vs.php');
  return $view->render();
}

/**
 * uiProgressBar
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
 * @param  $bars           Associative array definitions for bars
 * @param  $maxValue       The max value corresponds to 100% of the bar width, related to each bar's value
 * 
 * @return string          HTML markup 
 */
function ui_progress_bar(array $bars, $maxValue, $options = array())
{
  if (!is_int($maxValue))
  {
    throw new sfException('ui_progress_bar()  "maxValue" must be an integer');
  }
  
  // border color for the bar, override border-color from the stylesheet
  $innerDivOptions = array();
  if(isset($options['borderColor']))
  {
    // override background color on outer div
    $options['style'] = "background:${options['borderColor']};";
    // override border-color on inner div
    $innerDivOptions['style'] = "border-color:${options['borderColor']};";
    unset($options['borderColor']);
  }
  
  // merge widget class name
  $options['class'] = phpToolkit::merge_class_names(isset($options['class']) ? $options['class'] : array(), array('uiProgressBar'));
  
  // generate the bars as SPANs
  $spans = array();
  foreach($bars as $bar)
  {
    if (!ctype_digit((string)$bar['value']))
    {
      throw new sfException('ui_progress_bar()  "value" must be numeric');
    }
    
    if ($bar['value'] >= 0)
    {
      $percent = $bar['value'] > 0 ? ceil($bar['value'] / $maxValue * 100) : 0; 
      $label = isset($bar['label']) ? $bar['label'] : "${bar['value']}/${maxValue}";
      $spanOptions = array(
        'class' => isset($bar['class']) ? $bar['class'] : "g",
        'title' => $label,
        'style' => "width:${percent}%;"
      );
      array_push($spans, content_tag('span', $label, $spanOptions));
    }
  }
  
  $content = content_tag('div', implode('', $spans), $innerDivOptions);

  // generate the outer div
  return content_tag('div', $content, $options);
}
