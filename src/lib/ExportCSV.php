<?php
/**
 * ExportCSV - Export a coreDatabaseSelect query result to a CSV file.
 * 
 * 
 * @package    
 * @author     Fabrice Denis
 */

class ExportCSV
{
  protected
    $db        = null,
    $options   = array();
    
  const
    LINE_TERMINATED_BY = "\r\n",
    FIELDS_TERMINATED_BY = ",";

  /**
   * 
   * @return 
   */
  public function __construct($db)
  {
    $this->db = $db;
  }

  public static function escapeString($s)
  {
    // escape double quotes (CSV style)
    $s = preg_replace('/"/', '""', $s);

    // escape tabs
    $s = preg_replace('/\t/', '\t', $s);

    return $s;
  }

  public static function unescapeString($s)
  {
    // unescape CSV tabs
    $s = preg_replace('/\\t/', "\t", $s);

    // unescape CSV double quotes
    $s = preg_replace('/""/', '"', $s);

    return $s;
  }

  public static function quoteString($s, $escape = false)
  {
    $s = $escape ? self::escapeString($s) : $s;

    return '"' . $s . '"';
  }

  /**
   * Returns content of quoted string, optionally unescaping contents.
   *
   * @param  string $s 
   *
   * @return string
   */
  public static function unquoteString($s, $unescape = false)
  {
    // remove start and end double quote, if any, ignore space outside the quotes
    if (preg_match('/^[\s\r\n]*"(.*)"[\s\r\n]*$/', $s, $parts))
    {
      $s = $parts[1];
    }

    // unescape escaped double quotes
    $s = $unescape ? self::unescapeString($s) : $s;

    return $s;
  }
  
  /**
   * Export the query results to the current output buffer.
   * 
   * Options:
   *   col_escape:           Array of booleans, true means to escape as string, false means no escaping
   *   output_callback       Output callback function for ob_start() OPTIONAL (defaults to none)
   *                         In the Core framework should normally never use 'ob_gzhandler' because it is handled
   *                         at a higher level by the Web Response class.
   *   column_heads          Output column names in the first row OPTIONAL (boolean, defaults to true)
   *   row_callback          If not null is called on every row with the row data BEFORE it is escaped
   *                         and formatted for CSV. Function should return the row data. Do not change
   *                         the number of columns.
   * 
   * @param object $select   Select object
   * @param array  $columns  Array of column names as displayed in CSV,
   *                         must match the number of columns in the select
   * @param array  $options  Options (see above)
   */
  public function export(coreDatabaseSelect $select, $columns, $options = array())
  {
    $this->options = array_merge(array(
      'output_callback'  => null,
      'column_heads'     => true,
      'row_callback'     => null
    ), $options);

    // sanity check
    $row_callback = $this->options['row_callback'];
    if (null !== $row_callback && !is_callable($row_callback))
    {
      throw new sfException(__METHOD__."() invalid callback");
    }

    ob_start($this->options['output_callback']);

    $numColumns = count($columns);

    if (true === $this->options['column_heads'])
    {
      echo implode(self::FIELDS_TERMINATED_BY, $columns) . self::LINE_TERMINATED_BY;
    }

    // what columns to escape as strings
    $escapeCol = isset($options['col_escape']) ? $options['col_escape'] : null;

    $select->query();

    while ($row = $this->db->fetch(coreDatabase::FETCH_NUM))
    {
      $cells = array();
      
      // use callback if set
      if (null !== $row_callback)
      {
        $row = call_user_func($row_callback, $row);
      }

      for ($i = 0; $i < $numColumns; $i++)
      {
        $t = $row[$i];
        
        if ($escapeCol!==false && $escapeCol[$i])
        {
          // escape string values
          $t = self::quoteString($t, true);
          
        }
  
        $cells[] = $t;
      }
      
      echo implode(self::FIELDS_TERMINATED_BY, $cells) . self::LINE_TERMINATED_BY;
    }
  
    return ob_get_clean();
  }
}
