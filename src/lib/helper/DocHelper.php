<?php
/**
 * Documentation Helper.
 * 
 * Simple highlighting and formatting of <pre> blocks and print_r() output.
 * 
 * Example:
 * 
 *   <%php pre_start() %>
 *     // this is a comment
 *     // @return integer
 *     function do_something($options);
 *   <%php pre_end() %>
 * 
 * CSS rules for styling:
 * 
 *   pre span.comment
 *   pre span.var
 *   pre span.php
 *   pre span.static
 *   pre span.const
 *   pre span.string
 *   pre span.keyword
 *   pre span.phpdoc
 *   pre span.type
 *   
 *   pre.printr span.key
 *   pre.printr span.arrow    
 * 
 * 
 * @package  Helpers
 * @author   Fabrice Denis
 */

/**
 * Use to begin a preformatted code block.
 * 
 * By default creates <pre class="code">.
 * 
 * @return 
 * @param object $cssClass[optional]
 */
function pre_start($cssClass = 'code')
{
  echo '<pre class="'.$cssClass."\">\n";
  
  sfConfig::set('pre_highlight_mode', $cssClass);
  
    ob_start();
    ob_implicit_flush(false);
}

function pre_end()
{
  $text = ob_get_clean();

  // Highlight print_r results
  if (sfConfig::get('pre_highlight_mode')=='printr')
  {
    // highlight array keys
    $text = preg_replace('/\\[((.)*?)\\]/i', '<span class="key">\\1</span>', $text);
    $text = preg_replace('/=>/', '<span class="arrow">=></span>', $text);
  }
  else
  {
    // fix to avoid matching comments in urls
    $text = preg_replace('/:\/\//', ':&#47;&#47;', $text);

    // hightlight strings
    $text = preg_replace('/(["\'])((.)*?)\\1/i', '<span class="string">\\1\\2\\1</span>', $text);
  
    // highlight php tags
    $text = preg_replace('/&lt;\?php(\s+)/', '<span class="php">&lt;?php\\1</span>', $text);
    $text = preg_replace('/\?>/', '<span class="php">?&gt;</span>', $text);
    
    // highlight constants
    $text = preg_replace('/([A-Z_]{3,})/', '<span class="const">\\1</span>', $text);
  
    // highlight static function prefix
    $text = preg_replace('/(::)/', '<span class="static">\\1</span>', $text);
  
    // highlight C++ style comments
    $text = preg_replace('/(\/\/[^\r\n]+)/', '<span class="comment">\\1</span>', $text);

    // highlight C style comments
    $text = preg_replace('/\/\*((.|[\r\n])*?)\*\//', '<span class="comment">/*\\1*/</span>', $text);
  
    // highlight Perl style comments
    $text = preg_replace('/(\\s|^)(#[^\r\n]+)/', '\\1<span class="comment">\\2</span>', $text);

    // highlight variables
    $text = preg_replace('/(\$\w+)/', '<span class="var">\\1</span>', $text);

    // highlight some keywords
    $text = preg_replace('/(function|const)(\s+)/', '<span class="keyword">\\1</span>\\2', $text);
    
    // highlight PhpDoc keys
    $text = preg_replace('/@(return|param)\s+(\w+)/', '<span class="phpdoc">@\\1</span> <span class="type">\\2</span>', $text);
  }

  echo $text."</pre>\n";
}
