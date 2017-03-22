<?php
/**
 * A simple profiler for coreDatabaseMySQL queries showing time elapsed for
 * each query.
 *
 */

class coreDatabaseProfiler
{
  protected
    $log           = null,
    $totalElapsed  = 0;

  public function __construct()
  {
    $this->log = array();
  }

  public function getTotalNumQueries()
  {
    return count($this->log);
  }

  //abstract function getTotalElapsedSecs();

  //abstract function getQueryProfiles();

  /**
   *
   * @return  int     The total time spent running SQL queries (milliseconds).
   */
  public function getQueryTime()
  {
    return $this->totalElapsed;
  }

  public function getDebugLog()
  {
    $html = '<div style="background:#222;padding:5px 10px;font:bold 13px/1.3em &quot;Bitstream Vera Sans Mono&quot;, monospace;color:#aaa;overflow:hidden;">';

    // summary at top of the list
    $totalTime = $this->format_time_duration($this->totalElapsed);
    $html .= '<div style="color:yellow;font-weight:bold;">';
    $html .= count($this->log) . ' querries ('.$totalTime.'):</div>';

    foreach ($this->log as $query)
    {
      $time = $this->format_time_duration($query->getTime());

      $html .= '<br/>('.$time.') '.$this->formatSqlString($query->getQuery());
    }

    $html .= '</div>';

    return $html;
  }

  /**
   * Do some basic syntax highlighting on the query for legibility.
   *
   * @param   string  $sql
   * @return  string  
   */
  private function formatSqlString($s)
  {
    $s = preg_replace('/(SELECT|FROM|JOIN|WHERE|ORDER)/', '<span style="color:#6C6">$1</span>', $s);
    return $s;
  }


  /**
   * Time how long it takes for a particular piece of code to run.
   *
   * @return float The time taken
   */
  public function getExecutionTime()
  {
    static $time_start;

    $time = microtime(true);

    // Just starting timer, init and return
    if (!$time_start)
    {
      $time_start = $time;

      return;
    }

    // Timer has run, return execution time
    $total = $time - $time_start;
    if ($total < 0) {
      $total = 0;
    }
    $time_start = 0;

    return $total;
  }

  /**
   *
   * @param   string  $sql
   */
  public function logQuery($query)
  {
    $query .= ';';

    $time = $this->getExecutionTime();
    $this->totalElapsed += $time;

    $profile = new coreDatabaseProfilerQuery($query, $time);

    array_push($this->log, $profile);
  }

  /**
   * Format a decimal number in to microseconds, milliseconds, or seconds.
   *
   * @param   int     $time The time in microseconds
   *
   * @return  string  The friendly time duration
   */
  public function format_time_duration($time)
  {
    if(!is_numeric($time))
    {
      return '(na)';
    }

    if (round(1000000 * $time, 2) < 1000)
    {
      $time = number_format(round(1000000 * $time, 2))." μs";
    }
    elseif (round(1000000 * $time, 2) >= 1000 && round(1000000 * $time, 2) < 1000000)
    {
      $milliseconds = round((1000 * $time), 2);
      $style = $milliseconds >= 10 ? ' style="color:#f44;"' : '';
      $time = '<span'.$style.'>'.number_format($milliseconds).' ms</span>';
    }
    else
    {
      $time = '<span style="color:#f44;">'.round($time, 3).' seconds</span>';
    }

    return $time;
  }
}

class coreDatabaseProfilerMySQL extends coreDatabaseProfiler
{
}

// A single query profile.

class coreDatabaseProfilerQuery
{
  protected
    $query    = '',
    $time     = -1;

  /**
   *
   * @param   string  $query    The SQL query
   * @param   float   $time     Time elapsed as in microtime()
   */
  public function __construct($query, $time)
  {
    $this->query = $query;
    $this->time  = $time;
  }

  public function getTime()
  {
    return $this->time;
  }

  public function getQuery()
  {
    return $this->query;
  }
}
