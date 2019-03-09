<?php
/**
 * A class for testing php expressions and other misc. code with readable output.
 * 
 * 
 * @author  Fabrice Denis
 * @see     http://www.symfony-project.org/book/1_0/15-Unit-and-Functional-Testing
 */

class coreTest
{
  public $failed = 0;
  public $passed = 0;

  function title($title)
  {
    echo "<h2>$title</h2>\n";
  }

  /**
   * Compares two values and passes if they are equal (==)
   * 
   * @param mixed  Value
   * @param mixed  Expected value
   * @param string Test label
   */
  public function is($exp1, $exp2, $message = '')
  {
    if (is_object($exp1) || is_object($exp2)) {
      $value = $exp1 === $exp2;
    }
    else {
      $value = $exp1 == $exp2;
    }

    $result = $this->ok($value, $message);

    return $result;
  }

  public function ok($exp, $message = '')
  {
      if ($result = (boolean) $exp) {
        ++$this->passed;
      }
      else {
        ++$this->failed;
      }

    // print
    $message = ($message!=='') ? ('<div class="label">' . $message . '</div>') : ''; 

    $output = '<div class="result">' . $this->to_string($exp) . '</div>';

    $cssclass = $result ? 'box_ok' : 'box_error';

    echo <<<EOD
<div class="${cssclass}">
  $message
  $output
</div>
EOD;
  }

  /**
   * Output results with a message,
   * 
   * @return 
   */
  public function out($exp, $message = '')
  {
    $message = ($message!=='') ? ('<div class="label">' . $message . '</div>') : ''; 

    $output = '<div class="result">' . $this->to_string($exp) . '</div>';

    $cssclass = 'box_print';

    echo <<<EOD
<div class="${cssclass}">
  $message
  $output
</div>
EOD;
  }

  private function to_string($exp)
  {
    if (is_null($exp)) {
      $result = 'null';
    }
    elseif (is_bool($exp)) {
      $result = $exp ? 'true' : 'false';
    }
    elseif ($exp==='') {
      $result = '(empty string)';
    }
    else {
      $result = $exp;
    }
    return $result;
  }


  /**
   * Echo styles used by the tests output.
   * 
   */
  public function echoStyles()
  {
    echo <<< EOD
  <style type="text/css">
.box_print { margin:0.5em 0; padding:2px 9px; font-family:Courier New; font-size:12px; background:#D4EFFF;  border:1px solid #B1DDFF; }
.box_ok    { margin:0.5em 0; padding:2px 9px; font-family:Courier New; font-size:12px; background:#D6F7D4;  border:1px solid #A8F9A7; }
.box_error { margin:0.5em 0; padding:2px 9px; font-family:Courier New; font-size:12px; background:#FFCFDB;  border:1px solid #FFA6BA; }
.box_print .label { font-size:16px; font-weight:bold; color:#3787C9; margin:0 0 4px; }
.box_ok    .label { font-size:16px; font-weight:bold; color:#448844; margin:0 0 4px; }
.box_error .label { font-size:16px; font-weight:bold; color:#FF2E5B; margin:0 0 4px; }
  </style>
EOD;
  }
  
  /**
   * Outputs page footer with php script name and last modified time.
   * 
   * @return 
   */
  private function footer()
  {
    /*
    $pos = strrpos($_SERVER['PHP_SELF'], '/');
    if ($pos===false)
      $page = $_SERVER['PHP_SELF'];
    else
      $page = substr($_SERVER['PHP_SELF'], $pos+1);
    $last_modified = filemtime($page);
    $last_modified = date("d.m.Y", $last_modified);

    /* File: ${_SERVER['PHP_SELF']} - Last modified: ${last_modified} */

    echo <<< EOD
</body>
</html>
EOD;
  }
}
