<?php
/*
 * Helpers to output form tags with Bootstrap 5 classes.
 *
 * Reference:  https://getbootstrap.com/docs/5.0/forms/overview/
 *
 *
 * COMMON OPTIONS
 *
 *   All helpers pass the $options array to the Symfony tag helpers,
 *   so you can add any custom attributes, eg:
 *
 *     ['class' => 'max-w-[100px] text-sm', 'data-userid' => '1007']
 *
 *
 * INPUT OPTIONS
 *
 *   'helptext' => 'message'     To add a Bootstrap .form-text element after the input
 *   'label'                     Adds label  NOTE! input name = id
 *   'optional'                  Add an (Optional) text next to the label
 *
 *
 * COMMON HELPERS
 *
 *   _bs_button()
 *
 *
 * FORM HELPERS
 *
 *   _bs_formgroup([array $options], ...)
 *
 *    $options (optional) ... passed to Symfony tag helpers
 *
 *      'validate'        ... add "has-error" class to the form-group if
 *                            this input has a matching error in Symfony Request
 *
 *    Example:
 *      _bs_formgroup(['validate' => 'username'], ...)
 *
 *   _bs_input_checkbox($name, $options = array())
 *
 *      OPTIONAL  'label' => 'Label text'
 *      NOTE!     The input's `value` is ALWAYS "1"
 *
 *   _bs_input_email   ($name, $options = array())
 *   _bs_input_password($name, $options = array())
 *   _bs_input_text    ($name, $options = array())
 *   _bs_input_textarea($name, $options = array())
 *
 *   _bs_submit_tag    ($label, $options = array())
 *
 *
 * FORM LAYOUT
 *
 *   Inline: add class "form-control-i" to children of _bs_formgroup()
 *
 *
 * SEE
 *
 *   https://getbootstrap.com/docs/3.3/css/#forms
 *
 */

/**
 * Similar to sf's button_to(), but outputs a <button> (not an <input type=button>).
 *
 * DOES NOT DO ANY ESCAPING of the button's contents!
 *
 * <b>Options:</b>
 * - 'absolute' - if set to true, the helper outputs an absolute URL
 * - 'query_string' - to append a query string (starting by ?) to the routed url
 * - 'anchor' - to append an anchor (starting by #) to the routed url
 *
 * @see lib/vendor/symfony/lib/helper/UrlHelper.php   button_to()
 *
 * @param string $label        valid  for the button
 * @param string $internal_uri 'module/action' or '@rule' of the action
 * @param array  $options      additional HTML compliant <input> tag parameters
 */
function _bs_button($label, $internal_uri, array $options = [])
{
  $html_options = _parse_attributes($options);

  $url = url_for($internal_uri);

  if (isset($html_options['query_string']))
  {
    $url = $url.'?'.$html_options['query_string'];
    unset($html_options['query_string']);
  }

  if (isset($html_options['anchor']))
  {
    $url = $url.'#'.$html_options['anchor'];
    unset($html_options['anchor']);
  }

  $html_options['onclick'] = "document.location.href='".$url."';";
  $html_options = _convert_options_to_javascript($html_options);

  return content_tag('button', $label, $html_options);
}

// Classnames are appended to $options['class'] if present.
function _bs_class_merge(array &$options, $classnames)
{
  if (isset($options['class']))
  {
    if (!is_array($options['class']))
    {
      $classnames = $classnames.' '.$options['class'];
    }
    else
    {
      throw new sfException('_bs_class_merge() options["class"] must be a string');
    }
  }

  $options['class'] = $classnames;
}

/**
 * Optional first argument : array $options.
 *
 * Example:
 *
 *   _bs_form_group(
 *     array('style' => 'color:red'),
 *     '<span>Hello</span>',
 *     _bs_input_text('Label', 'name_and_id')
 *   )
 *
 * @param array|string $options Options (array), or html to wrap inside form-group
 * @param string       ...$html One or more html to append inside form-group
 */
function _bs_form_group()
{
  if (func_num_args() < 1)
  {
    throw new sfException('_bs_form_group() has no content.');
  }

  $args = func_get_args();
  $input_name = false;
  $hasErrorMsg = '';

  // pull the optional first argument : array $options
  $options = is_array($args[0]) ? array_shift($args) : [];

  $merge_class = 'form-group';

  // add Bootstrap 'has-error' class
  if (false !== ($input_name = $options['validate'] ?? false))
  {
    unset($options['validate']);
    $request = sfContext::getInstance()->getRequest();
    if ($request->hasError($input_name))
    {
      $hasErrorMsg = '<div class="invalid-feedback">^ '.$request->getError($input_name).'</div>';
    }
  }

  _bs_class_merge($options, $merge_class);

  $html = "\n<div "._tag_options($options).'>'
        .implode($args)
        .$hasErrorMsg
        ."\n</div>";

  return $html;
}

function _bs_input($type, $name, $options = [])
{
  $html = [];

  if (null !== ($label = _get_option($options, 'label')))
  {
    $html[] = "\n  ".label_for($name /* id */, $label, ['class' => 'form-label']);
  }

  if (null !== ($optional = _get_option($options, 'optional')))
  {
    $html[] = '<span class="form-optional">(optional)</span>';
  }

  // input
  _bs_class_merge($options, 'form-control');

  // FIXME  obsolete FormHelper (not the Symfony one) did not include 'id'
  $options['id'] = get_id_from_name($name);

  if ($type === 'text'
      || $type === 'email' /* skip the annoying browser-based email checking */)
  {
    $html[] = "\n  ".input_tag($name, '', $options);
  }
  elseif ($type === 'password')
  {
    $html[] = "\n  ".input_password_tag($name, null, $options);
  }
  elseif ($type === 'textarea')
  {
    $html[] = "\n  ".textarea_tag($name, null, $options);
  }
  else
  {
    throw new sfException('Unsupported input type in _bs_input()');
  }

  // help text
  if (null !== ($helptext = _get_option($options, 'helptext')))
  {
    $html[] = "\n  ".'<span class="form-text">'.$helptext.'</span>';
  }

  return implode($html);
}

/**
 * Note: we diverge from Bootstrap here and output a .form-group for consistency with
 * the form row helper, as well as use a span, so we can use display:table to fix the
 * freakin checkbox/label alignment.
 *
 *  <div class="form-group">
 *    <label><input ...><span>Label text</span>
 *  </div>
 *
 * @param mixed $name
 * @param mixed $options
 */
function _bs_input_checkbox($name, $options = [])
{
  // we want a wrapping label here
  if (null !== ($label = _get_option($options, 'label')))
  {
    $html[] = "\n  <label>";
  }

  $html[] = checkbox_tag($name, '1', false, $options)."<span>{$label}</span>";

  if (null !== $label)
  {
    $html[] = "</label>\n";
  }

  return implode($html);
}

function _bs_input_email($name, $options = [])
{
  return _bs_input('text', $name, $options);
}

function _bs_input_password($name, $options = [])
{
  return _bs_input('password', $name, $options);
}

function _bs_input_text($name, $options = [])
{
  return _bs_input('text', $name, $options);
}

function _bs_input_textarea($name, $options = [])
{
  return _bs_input('textarea', $name, $options);
}

/**
 * Returns <input type=submit" ...> - adds 'success' button styles by
 * default unless a `ko-Btn` class is used in the `class` option.
 *
 * @param string $label
 * @param array  $options Symfony tag helper options
 *
 * @return string
 */
function _bs_submit_tag($label, $options = [])
{
  // default to adding `success` style - unless a button class is used
  $classnames = $options['class'] ?? '';
  if (false === strstr($classnames, 'ko-Btn'))
  {
    _bs_class_merge($options, 'ko-Btn ko-Btn--success');
  }

  return submit_tag($label, $options);
}

function koohii_onload_slot()
{
  $name = 'koohii_onload_js';
  $prevContent = get_slot($name);
  slot($name);
  echo $prevContent;
  // echo "console.log('koohii_onload_slot()')\n";
}

function koohii_onload_slots_out()
{
  if ($s = get_slot('koohii_onload_js'))
  {
    echo "<script>\n",
    '/* Koohii onload slot */ ',
    "window.addEventListener('DOMContentLoaded',function(){\n", $s, "});</script>\n";
  }
}

define('KK_GLOBALS', 'kk.globals');

/**
 * Helper to "hydrate" template with data for the frontend.
 *
 * Use `kk_globals_get()` in Javascript (cf. globals.d.ts)
 *
 * Conveniently, this hydration happens BEFORE defered modules
 * from Vite build are run, since defered modules happen after
 * the document is parsed, and <script>'s are part of the document.
 *
 * @param string $name  the key name (convention ALL_UPPERCASE)
 * @param mixed  $value any valid value that parses to JSON (string, boolean, null, etc)
 */
function kk_globals_put(string $name, $value)
{
  $kk_globals = sfConfig::get(KK_GLOBALS);
  if (null === $kk_globals)
  {
    $kk_globals = new sfParameterHolder();
    sfConfig::set(KK_GLOBALS, $kk_globals);
  }
  $kk_globals->set($name, $value);
}

/**
 * Call once in the main layout template to output all KK.* globals.
 */
function kk_globals_out()
{
  kk_globals_put('BASE_URL', url_for('@homepage', true));

  if (null !== ($kk_globals = sfConfig::get(KK_GLOBALS)))
  {
    $values = json_encode($kk_globals->getAll());

    $lines = [];
    foreach ($kk_globals->getAll() as $name => $value)
    {
      $lines[] = "KK.{$name} = ".json_encode($value, JSON_UNESCAPED_SLASHES);
    }

    echo "\n<script>\nwindow.KK || (KK = {});\n".implode("\n", $lines)."\n</script>\n";
  }
}

/**
 * Include FontAwesome 5 webfonts.
 *
 *   - github (public) repo points to the free CDN version
 *
 *   - private repo has the pro download with all icons
 *     (temporarily uncomment code below to enable all icons)
 *
 *   - production should use the "subset" version compiled
 *     locally with the subsetter tool
 */
function include_fontawesome()
{
  // TEMPORARILY uncomment this to test all pro icons (private repo)
  // echo '<link href="/fonts/fa5pro/css/all-but-duo.min.css" rel="stylesheet">';
  // return;

  if (KK_ENV_FORK)
  {
    // use the free, CDN version
    echo '<link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">';
  }
  else
  {
    // use the pro "subset" version for reduced file sizes
    echo '<link href="/fonts/fa5sub/css/all.min.css" rel="stylesheet">';
  }
}

function query_string_for_review(array $queryParams)
{
  return http_build_query($queryParams);
}

/**
 * returns url for the review page with given query parameters.
 *
 * @return string
 */
function url_for_review(array $queryParams)
{
  $queryString = query_string_for_review($queryParams);

  return url_for('@review', ['absolute' => true]).'?'.$queryString;
}
