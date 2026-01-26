<?php
/**
 * (Zend_Db like API from the old RevTK codebase)
 *
 * coreDatabase is an abstraction class that allows to setup a database
 * connection via configuration parameters, and then access the data
 * with SQL querries or through the coreDatabaseSelect object.
 *
 * Binding parameters : all methods that accept a query, or a where
 * clause also accept binding parameters. The question marks in the
 * query string are replaced by the parameters. The $bind argument should
 * be a single value, or an array.
 *
 * All parameters substituted in this way are automatically quoted.
 * When you need to pass a SQL expression as a bound parameter, wrap it
 * with "new coreDbExpr(...)" so that this parameter is not quoted.
 *
 *
 * @author     Fabrice Denis
 */

abstract class coreDatabase
{
  /**
   * Fetch mode to use for fetch(), fetchRow() and fetchAll()
   *
   * FETCH_ASSOC: return data in an array of associative arrays. The array keys are
   *              column names, as strings. This is the default fetch mode.
   *
   * FETCH_OBJ:   return data in an array of objects. The default class is the PHP built-in
   *              class stdClass. Columns of the result set are available as public properties of the object.
   */
  const FETCH_NUM   = 1;
  const FETCH_ASSOC = 2;
  const FETCH_OBJ   = 3;
    
  protected
    $parameterHolder = null,
    $connection      = null,
    $profiler        = null;
    
  /**
   * Class constructor.
   *
   * @see initialize()
   */
  public function __construct($parameters = [])
  {
    $this->initialize($parameters);
  }

  /**
   * Initializes this coreDatabase object.
   *
   * @param array An associative array of initialization parameters
   *
   */
  public function initialize($parameters = [])
  {
    $this->parameterHolder = new sfParameterHolder();
    $this->parameterHolder->add($parameters);

    // setup query profiler
    $user = sfContext::getInstance()->getUser();
    if (null !== $user /*wtf*/ && ($user->getUserName() === 'fuaburisu' || $user->isAdministrator()))
    {
      $this->profiler = new coreDatabaseProfilerMySQL();
    }
  }

  /**
   * Creates a connection to the database.
   *
   * @throws sfException  If a connection could not be created
   * @return void
   */
  abstract function connect();

  /**
   * Returns the underlying database connection object or resource.
   * If not presently connected, returns null
   *
   * @return object|resource|null
   */
  abstract function getConnection();

  /**
   * Returns the SQL query profiler instance.
   *
   * @return  coreDatabaseProfiler
   */
  public function getProfiler()
  {
    return $this->profiler;
  }

  /**
   * Gets the parameter holder for this object.
   *
   * @return coreParameterHolder A coreParameterHolder instance
   */
  public function getParameterHolder()
  {
    return $this->parameterHolder;
  }

  /**
   * Gets the parameter associated with the given key.
   *
   * This is a shortcut for:
   *
   * <code>$this->getParameterHolder()->get()</code>
   *
   * @param string The key name
   * @param string The default value
   *
   * @return string The value associated with the key
   *
   * @see sfParameterHolder
   */
  public function getParameter($name, $default = null)
  {
    return $this->parameterHolder->get($name, $default);
  }

  /**
   * Returns number of rows in resultset from the last SQL query.
   *
   * @return mixed  The number of rows, or FALSE on failure.
   */
  abstract function num_rows();

  /**
   * Run a SQL query directly.
   *
   * @param  string         SQL query string where '?' can be used for quoted parameters.
   * @param  string|array   Parameters to substitute in the query string
   *
   * @return bool   True if success, False if error.
   */
  abstract function query($query, $bind = null);

  /**
   * Start building a new query with the coreDatabaseSelect object.
   *
   * @return coreDatabaseSelect query object for building queries
   */
  abstract function select($columns = null);

  /**
   * Set the default fetch mode for fetch(), fetchRow() and fetchAll().
   *
   * The function returns the last fetch mode.
   *
   * @param  int  $fetchMode  coreDatabase::FETCH_ASSOC or coreDatabase::FETCH_OBJ
   * @return int  Last active fetch mode.
   */
  public function setFetchMode($mode)
  {
    $prevFetchMode = $this->fetchMode;
    switch ($mode)
    {
      case self::FETCH_NUM:
      case self::FETCH_ASSOC:
      case self::FETCH_OBJ:
        $this->fetchMode = $mode;
        break;
      default:
        throw new sfException('Invalid fetch mode.');
        break;
    }
    return $prevFetchMode;
  }

  public function getFetchMode()
  {
    return $this->fetchMode;
  }

  /**
   * Fetches the next row from the last query() result set.
   *
   * If no fetch mode is provided, it uses the last set or default fetch mode (FETCH_ASSOC).
   *
   * @param int|null $fetchMode Optional. The fetch mode to use. Can be one of:
   *                            - self::FETCH_NUM: Fetch as a numeric array
   *                            - self::FETCH_OBJ: Fetch as an object
   *                            - self::FETCH_ASSOC: Fetch as an associative array
   *
   * @return array|object|false Returns the fetched row as an array or object depending on 
   *                            the fetch mode, or false if there are no more rows or on error.
   */
  abstract function fetch($fetchMode = null);

  /**
   * Fetches the next row and returns it as an object.
   *
   * @param string $class  OPTIONAL Name of the class to create.
   * @param array  $config OPTIONAL Constructor arguments for the class.
   *
   * @return mixed  One object instance of the specified class, or false.
   */
  abstract function fetchObject($class = 'stdClass', array $config = []);

  /**
   * Returns first column from first row of the result set (useful for "count(*)" querries)
   *
   * @return mixed  One value from the first row of result set, or false.
   */
  abstract function fetchOne($query, $bind = null);

  /**
   * Returns the first row of the result set as an object, or false
   *
   * @return mixed Resultset row as an object, or FALSE if no results.
   */
  abstract function fetchRow($query, $bind = null);

  /**
   * Fetches all SQL result rows as a sequential array.
   * Returned row format depends on the current fetchMode.
   *
   * @param  string|coreDatabaseSelect  $sql  An SQL SELECT statement.
   * @param  mixed            $bind Data to bind into SELECT placeholders.
   *
   * @return array
   */
  abstract function fetchAll($query, $bind = null);

  /**
   * Fetches the first column of all result rows as an array.
   *
   * The first column in each row is used as the array key.
   *
   * @param  string|coreDatabaseSelect  $sql  An SQL SELECT statement.
   * @param  mixed  $bind   Data to bind into SELECT placeholders.
   *
   * @return array
   */
  abstract function fetchCol($query, $bind = null);

  /**
   * Insert a new row with specified key => values,
   * ignored columns get default values as per SQL TABLE creation.
   *
   * @see    lastInsertId() to retrieve an auto_increment key
   *
   * @param  string  Table name.
   * @param  array   An associative array of properties (column names) and data.
   * @return boolean TRUE on success, FALSE on error.
   * @throws sfException  If query fails.
   */
  abstract function insert($table, $data = []);

  /**
   * Retrieves the id generated for an AUTO_INCREMENT column by the previous
   * insert() operation.
   *
   * Returns 0 if the previous query did not generate an AUTO_INCREMENT value.
   *
   * @return int  AUTO_INCREMENT id, or 0
   */
  abstract function lastInsertId();

  /**
   * Updates columns (key => values) in matching row(s) with optional where clause
   *
   * @param  string  Table name.
   * @param  array   An associative array of properties (column names) and data.
   * @param  string  Where clause with optional '?' quoted parameters.
   * @param  mixed   Single value or array of values for quoted parameters.
   * @return boolean TRUE on success, FALSE on error.
   * @throws sfException  If query fails.
   */
  abstract function update($table, $data, $where = null, $bind = null);

  /**
   * Delete all rows, or matching rows with optional where clause
   *
   * @param  string  Table name.
   * @param  string  Where clause with optional '?' quoted parameters.
   * @param  mixed   Single value or array of values for quoted parameters.
   * @return boolean TRUE on success, FALSE on error.
   * @throws sfException  If query fails.
   */
  abstract function delete($table, $where = null, $bind = null);

  /**
   * Safely quotes a value for an SQL statement using database specific implementation.
   *
   * @param object $value
   * @return
   */
  abstract function quote($value);


  /**
   * Output a html table with the resultset (or single rowdata), including column names
   *
   * @param $resultset Array of objects or assoc.arrays
   */
  abstract function dumpResultSet($resultset);
}

/**
 * The coreDbExpr object is used to wrap parameters in querries which should not be quoted
 * (such as SQL expressions).
 *
 * Example:
 *   $db->insert($tblName, array('name' => 'John', 'updated_on' => new coreDbExpr('NOW()')));
 */
class coreDbExpr
{
  protected $_expression;

  public function __construct($expression)
  {
    $this->_expression = (string) $expression;
  }

  public function __toString()
  {
    return $this->_expression;
  }
}

/**
 * The coreDatabaseSelect object allows to construct sql queries in a programatic way,
 * parts of the query can be added selectively, or even reset.
 *
 * The __toString() method will return the SQL query
 *
 * The select object is usually returned from Database::select() method, to which
 * columns can be directly passed. It can also be created directly, in which case
 * you need to call the columns() method, and pass the Database reference
 * when creating the new coreDatabaseSelect object.
 *
 * Each method returns the instance, so the methods can be chained like this :
 *
 * $select->from(...)->where(...)->order(...);
 *
 * Or even :
 *
 * $select->from(...)
 *        ->where(...)
 *        ->order(...);
 * 
 * @see    http://framework.zend.com/manual/en/zend.db.select.html
 *
 * @todo   Support offset without LIMIT. Does it work in MySQL?
 */
class coreDatabaseSelect
{
  // holds a reference to the Database layer
  private $db;
  // holds parts of the query used to construct the sql string
  private $parts;

  /**
   * Parts of the select that can be reset
   * @see reset()
   */
  const COLUMNS      = 'columns';
  const FROM         = 'from';
  const JOINS        = 'joins';
  const WHERE        = 'where';
  const GROUP        = 'group';
  const HAVING       = 'having';
  const ORDER        = 'order';
  const LIMIT_COUNT  = 'limitcount';
  const LIMIT_OFFSET = 'limitoffset';

  /**
   * Initialize the SELECT statement, columns can be a string (single column) or array (multiple columns)
   *
   * @param coreDatabase $db
   * @param string|array $columns 
   */
  public function __construct(coreDatabase $db, $columns = null)
  {
    $this->db = $db;
    $this->reset();
    if (null !== $columns)
    {
      $this->columns($columns);
    }
  }

  /**
   * @return string
   */
  public function __toString()
  {
    // COLUMNS
    $columns = $this->parts[self::COLUMNS];
    if (empty($columns))
    {
      $columns = ['*'];
    }
    $q = 'SELECT ' .  $this->db->aliases($columns);

    // FROM clause
    if (isset($this->parts[self::FROM])) {
      $q = $q . ' FROM ' . $this->db->aliases($this->parts[self::FROM]);
    }

    // JOIN clauses
    if (count($this->parts[self::JOINS])) {
      foreach ($this->parts[self::JOINS] as $joinString) {
        $q = $q . $joinString;
      }
    }

    // WHERE clause
    if (count($this->parts[self::WHERE])) {
      $q = $q . ' WHERE (' . implode(') AND (', $this->parts[self::WHERE]) . ')';
    }

    // GROUP clause
    if (isset($this->parts[self::GROUP])) {
      $q = $q . ' GROUP BY ' . implode(',', $this->parts[self::GROUP]);
    }

    // HAVING clause
    if (count($this->parts[self::HAVING])) {
      $q = $q . ' HAVING (' . implode(') AND (', $this->parts[self::HAVING]) . ')';
    }

    // ORDER clause
    if (isset($this->parts[self::ORDER])) {
      $q = $q . ' ORDER BY ' . implode(',', $this->parts[self::ORDER]);
    }

    // LIMIT clause
    if (isset($this->parts[self::LIMIT_COUNT])) {
      $q = $q . ' LIMIT ';
      if (isset($this->parts[self::LIMIT_OFFSET])) {
        $q = $q . $this->parts[self::LIMIT_OFFSET] . ', ';
      }
      $q = $q . $this->parts[self::LIMIT_COUNT];
    }

    return $q;
  }

  /**
   * Reset part of the query, or all parts if no argument is passed.
   *
   * Note: If you reset all parts then you need to use the ->columns() method to add
   * columns (usually it's easier to pass columns to the select() method of coreDatabase).
   *
   *
   * @param $part Which part to reset (see class constants)
   * @return self
   */
  public function reset($part = null)
  {
    if ($part===null)
    {
      $this->parts = [];
      $this->parts[self::COLUMNS] = [];
      $this->parts[self::JOINS]   = [];
      $this->parts[self::WHERE]   = [];
      $this->parts[self::HAVING]  = [];
    }
    else
    {
      switch ($part)
      {
        case self::FROM:
        case self::GROUP:
        case self::ORDER:
        case self::LIMIT_COUNT:
        case self::LIMIT_OFFSET:
          $this->parts[$part] = null;
          break;
        case self::COLUMNS:
        case self::JOINS:
        case self::WHERE:
        case self::HAVING:
          $this->parts[$part] = [];
          break;
        default:
          throw new Exception('coreDatabaseSelect::reset() Invalid argument.');
          break;
      }
    }
    return $this;
  }

  /**
   * Adds to the columns
   *
   * @param string|array $cols
   * @return self
   */
  public function columns($cols)
  {
    if (!is_array($cols))
    {
      $cols = [$cols];
    }
    $this->parts[self::COLUMNS] = array_merge($this->parts[self::COLUMNS], $cols);
    return $this;
  }

  /**
   * Specify FROM table(s).
   *
   * Ex1:   'table1'
   * Ex2:   array('t' => 'table')
   * Ex3:   array('table1', 't2' => 'table2')
   * 
   * @return self
   */
  public function from($table)
  {
    $this->parts[self::FROM] = $table;
    return $this;
  }

  /**
   *
   *
   * @param $table Object
   * @return self
   */
  public function join($table, $condition)
  {
    $this->parts[self::JOINS][] = ' JOIN ' . $this->db->aliases($table) . ' ON ' . $condition;
    return $this;
  }

  /**
   *
   *
   * @param string $table    The table to join
   * @param mixed  $columns  Column(s) for the USING clause (string or array)
   * @return self
   */
  public function joinUsing($table, $columns)
  {
    $this->parts[self::JOINS][] = ' JOIN ' . $this->db->aliases($table) . ' USING(' . $this->db->aliases($columns) . ')';
    return $this;
  }

  /**
   *
   *
   * @param $table Object
   * @return self
   */
  public function joinLeft($table, $condition)
  {
    $this->parts[self::JOINS][] = ' LEFT JOIN ' . $this->db->aliases($table) . ' ON ' . $condition;
    return $this;
  }

  /**
   *
   * @param string $table    The table to join
   * @param mixed  $columns  Column(s) for the USING clause (string or array)
   * @return self
   */
  public function joinLeftUsing($table, $columns)
  {
    $this->parts[self::JOINS][] = ' LEFT JOIN ' . $this->db->aliases($table) . ' USING(' . $this->db->aliases($columns) . ')';
    return $this;
  }

  /**
   * 
   * @return self
   */
  public function where($criteria, $bindParams = null)
  {
    if (func_num_args()>2) {
      DBG::error(__METHOD__.' Argument #2 should be an array if there is more than one bound parameter.');
    }

    $this->parts[self::WHERE][] = $this->db->bind($criteria, $bindParams);
    return $this;
  }
  
  /**
   * Add a WHERE col IN (values) clause.
   * 
   * FIXME? Only needed with integer values so far.
   *
   * @param string $column 
   * @param int[] $values
   * @return self
   */
  public function whereIn($column, $values)
  {
    assert(is_array($values));
    $this->parts[self::WHERE][] = $column.' IN ('.implode(',', $values).')';
    return $this;
  }

  /**
   * 
   * @return self
   */
  public function group($columns)
  {
    $this->parts[self::GROUP] = (array)$columns;
    return $this;
  }

  /**
   * 
   * @return self
   */
  public function having($criteria, $bindParams = null)
  {
    $this->parts[self::HAVING][] = $this->db->bind($criteria, $bindParams);
    return $this;
  }

  /**
   * Add a ORDER BY clause to the query.
   *
   * Direction can be added simply in the column name eg: ->order('age DESC')
   *
   * @return self
   */
  public function order($columns)
  {
    $this->parts[self::ORDER] = (array)$columns;
    return $this;
  }

  /**
   * 
   * @return self
   */
  public function limit($numrows, $offset = null)
  {
    $this->parts[self::LIMIT_COUNT] = $numrows;
    $this->parts[self::LIMIT_OFFSET] = $offset;
    return $this;
  }

  /**
   * Convenient way to apply paging
   *
   * @param int $pageNum zero-based page number
   * @param int $rowsPerPage
   * @return self
   */
  public function limitPage($pageNum, $rowsPerPage)
  {
    assert(is_int($pageNum) && is_int($rowsPerPage));
    $this->parts[self::LIMIT_COUNT] = $rowsPerPage;
    $this->parts[self::LIMIT_OFFSET] = $rowsPerPage * $pageNum;
    return $this;
  }

  /**
   * Executes the query.
   *
   * Since this is a shortcut to coreDatabase query(), it is normally used
   * with the fetch() and fetchObject() methods for retrieving rows of the result set.
   * Rows are returned in the current fetch mode set by coreDatabase::setFetchMode()
   *
   * @return boolean  True on success, or throws sfException
   */
  public function query()
  {
    return $this->db->query($this->__toString());
  }
}


/**
 * coreDatabaseStatement allows to run prepared statements.
 *
 * Basic implementation using the same interface as Zend_Db_Statement,
 * does not support named parameters.
 *
 * @author     Fabrice Denis
 * 
 * 
 */
abstract class coreDatabaseStatement
{
  protected $_adapter = null;

  public function __construct(coreDatabase $adapter, $sql)
  {
    $this->_adapter = $adapter;
    $this->_prepare($sql);
  }

  /**
   * Executes a prepared statement.
   *
   * @param  array $params OPTIONAL Values to bind to parameter placeholders.
   *
   * @return bool  TRUE on success or FALSE on failure.
   */
  public function execute(array $params = null)
  {
    return $this->_execute($params);
  }

  /**
   * Returns the number of rows affected by the last INSERT, UPDATE,
   * REPLACE or DELETE query.
   *
   * For SELECT statements mysqli_affected_rows() works like mysqli_num_rows()
   *
   * @return int     The number of rows affected.
   */
  abstract function rowCount();
}
