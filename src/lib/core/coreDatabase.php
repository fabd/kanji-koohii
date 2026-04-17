<?php
/**
 * (Zend_Db like API from the old RevTK codebase).
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
 * @author     Fabrice Denis
 */
abstract class coreDatabase
{
  /**
   * Fetch mode to use for fetch(), fetchRow() and fetchAll().
   *
   * FETCH_ASSOC: return data in an array of associative arrays. The array keys are
   *              column names, as strings. This is the default fetch mode.
   *
   * FETCH_OBJ:   return data in an array of objects. The default class is the PHP built-in
   *              class stdClass. Columns of the result set are available as public properties of the object.
   */
  public const FETCH_NUM   = 1;
  public const FETCH_ASSOC = 2;
  public const FETCH_OBJ   = 3;

  protected sfParameterHolder $parameterHolder;
  protected mixed $connection                   = null;
  protected ?coreDatabaseProfiler $profiler     = null;
  protected int $fetchMode                      = self::FETCH_ASSOC;

  /**
   * Class constructor.
   *
   * @see initialize()
   *
   * @param mixed $parameters
   */
  public function __construct($parameters = [])
  {
    $this->initialize($parameters);
  }

  /**
   * Initializes this coreDatabase object.
   *
   * @param array $parameters An associative array of initialization parameters
   */
  public function initialize($parameters = [])
  {
    $this->parameterHolder = new sfParameterHolder();
    $this->parameterHolder->add($parameters);

    // setup query profiler
    $user = kk_get_user();
    if ($user->getUserName() === 'fuaburisu' || $user->isAdministrator()) {
      $this->profiler = new coreDatabaseProfilerMySQL();
    }
  }

  /**
   * Creates a connection to the database.
   *
   * @throws sfException If a connection could not be created
   */
  abstract public function connect();

  /**
   * Returns the underlying database connection object or resource.
   * If not presently connected, returns null.
   *
   * @return object|resource|null
   */
  abstract public function getConnection();

  /**
   * Returns the SQL query profiler instance.
   *
   * @return null|coreDatabaseProfiler
   */
  public function getProfiler()
  {
    return $this->profiler;
  }

  /**
   * Gets the parameter holder for this object.
   *
   * @return sfParameterHolder
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
   * @param string $name    The key name
   * @param string $default The default value
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
   * @return mixed the number of rows, or FALSE on failure
   */
  abstract public function num_rows();

  /**
   * Run a SQL query directly.
   *
   * @param string       $query SQL query string where '?' can be used for quoted parameters
   * @param array|string $bind  Parameters to substitute in the query string
   *
   * @return bool true if success, False if error
   */
  abstract public function query($query, $bind = null);

  /**
   * Start building a new query with the coreDatabaseSelect object.
   *
   * @param mixed|null $columns
   *
   * @return coreDatabaseSelect query object for building queries
   */
  abstract public function select($columns = null);

  /**
   * Set the default fetch mode for fetch(), fetchRow() and fetchAll().
   *
   * The function returns the last fetch mode.
   *
   * @param int $mode coreDatabase::FETCH_ASSOC or coreDatabase::FETCH_OBJ
   *
   * @return int last active fetch mode
   */
  public function setFetchMode($mode)
  {
    $prevFetchMode = $this->fetchMode;

    switch ($mode) {
      case self::FETCH_NUM:
      case self::FETCH_ASSOC:
      case self::FETCH_OBJ:
        $this->fetchMode = $mode;

        break;

      default:
        throw new sfException('Invalid fetch mode.');
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
   * @return array|false|object returns the fetched row as an array or object depending on
   *                            the fetch mode, or false if there are no more rows or on error
   */
  abstract public function fetch($fetchMode = null);

  /**
   * Fetches the next row and returns it as an object.
   *
   * @param string $class  OPTIONAL Name of the class to create
   * @param array  $config OPTIONAL Constructor arguments for the class
   *
   * @return mixed one object instance of the specified class, or false
   */
  abstract public function fetchObject($class = 'stdClass', array $config = []);

  /**
   * Returns first column from first row of the result set (useful for "count (*)" querries).
   *
   * @param mixed      $query
   * @param mixed|null $bind
   *
   * @return mixed one value from the first row of result set, or false
   */
  abstract public function fetchOne($query, $bind = null);

  /**
   * Returns the first row of the result set as an object, or false.
   *
   * @param mixed      $query
   * @param mixed|null $bind
   *
   * @return mixed resultset row as an object, or FALSE if no results
   */
  abstract public function fetchRow($query, $bind = null);

  /**
   * Fetches all SQL result rows as a sequential array.
   * Returned row format depends on the current fetchMode.
   *
   * @param coreDatabaseSelect|string $query an SQL SELECT statement
   * @param mixed                     $bind  data to bind into SELECT placeholders
   *
   * @return array
   */
  abstract public function fetchAll($query, $bind = null);

  /**
   * Fetches the first column of all result rows as an array.
   *
   * The first column in each row is used as the array key.
   *
   * @param coreDatabaseSelect|string $query an SQL SELECT statement
   * @param mixed                     $bind  data to bind into SELECT placeholders
   *
   * @return array
   */
  abstract public function fetchCol($query, $bind = null);

  /**
   * Insert a new row with specified key => values,
   * ignored columns get default values as per SQL TABLE creation.
   *
   * @see    lastInsertId() to retrieve an auto_increment key
   *
   * @param string $table table name
   * @param array  $data  an associative array of properties (column names) and data
   *
   * @return bool TRUE on success, FALSE on error
   *
   * @throws sfException if query fails
   */
  abstract public function insert($table, $data = []);

  /**
   * Retrieves the id generated for an AUTO_INCREMENT column by the previous
   * insert() operation.
   *
   * Returns 0 if the previous query did not generate an AUTO_INCREMENT value.
   *
   * @return int AUTO_INCREMENT id, or 0
   */
  abstract public function lastInsertId();

  /**
   * Updates columns (key => values) in matching row(s) with optional where clause.
   *
   * @param string $table table name
   * @param array  $data  an associative array of properties (column names) and data
   * @param string $where where clause with optional '?' quoted parameters
   * @param mixed  $bind  single value or array of values for quoted parameters
   *
   * @return bool TRUE on success, FALSE on error
   *
   * @throws sfException if query fails
   */
  abstract public function update($table, $data, $where = null, $bind = null);

  /**
   * Delete all rows, or matching rows with optional where clause.
   *
   * @param string $table table name
   * @param string $where where clause with optional '?' quoted parameters
   * @param mixed  $bind  single value or array of values for quoted parameters
   *
   * @return bool TRUE on success, FALSE on error
   *
   * @throws sfException if query fails
   */
  abstract public function delete($table, $where = null, $bind = null);

  /**
   * Safely quotes a value for an SQL statement using database specific implementation.
   *
   * @param mixed $value
   *
   * @return string
   */
  abstract public function quote($value);

  /**
   * Creates a SQL string of selected columns or expressions.
   * Array keys become AS aliases: ['alias' => 'expr'] → "expr AS alias".
   *
   * @param array|string $columns
   *
   * @return string
   */
  abstract public function aliases($columns);

  /**
   * Bind and quote parameters into a query string.
   * Each '?' placeholder is replaced by the corresponding quoted parameter.
   *
   * @param string     $query
   * @param mixed|null $bindParams Single value or array of values
   *
   * @return string
   */
  abstract public function bind($query, $bindParams);

  /**
   * Output a html table with the resultset (or single rowdata), including column names.
   *
   * @param $resultset Array of objects or assoc.arrays
   */
  abstract public function dumpResultSet($resultset);
}

/**
 * The coreDbExpr object is used to wrap parameters in querries which should not be quoted
 * (such as SQL expressions).
 *
 * Example:
 *   $db->insert($tblName, ['name' => 'John', 'updated_on' => new coreDbExpr('NOW()')]);
 */
class coreDbExpr implements Stringable
{
  public function __construct(
    private readonly string $expression,
  ) {}

  public function __toString(): string
  {
    return $this->expression;
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
   * Parts of the select that can be reset.
   *
   * @see reset()
   */
  public const COLUMNS      = 'columns';
  public const FROM         = 'from';
  public const JOINS        = 'joins';
  public const WHERE        = 'where';
  public const GROUP        = 'group';
  public const HAVING       = 'having';
  public const ORDER        = 'order';
  public const LIMIT_COUNT  = 'limitcount';
  public const LIMIT_OFFSET = 'limitoffset';

  /**
   * Initialize the SELECT statement, columns can be a string (single column) or array (multiple columns).
   *
   * @param array|string $columns
   */
  public function __construct(coreDatabase $db, $columns = null)
  {
    $this->db = $db;
    $this->reset();
    if (null !== $columns) {
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
    if (empty($columns)) {
      $columns = ['*'];
    }
    $q = 'SELECT '.$this->db->aliases($columns);

    // FROM clause
    if (isset($this->parts[self::FROM])) {
      $q = $q.' FROM '.$this->db->aliases($this->parts[self::FROM]);
    }

    // JOIN clauses
    if (count($this->parts[self::JOINS])) {
      foreach ($this->parts[self::JOINS] as $joinString) {
        $q = $q.$joinString;
      }
    }

    // WHERE clause
    if (count($this->parts[self::WHERE])) {
      $q = $q.' WHERE ('.implode(') AND (', $this->parts[self::WHERE]).')';
    }

    // GROUP clause
    if (isset($this->parts[self::GROUP])) {
      $q = $q.' GROUP BY '.implode(',', $this->parts[self::GROUP]);
    }

    // HAVING clause
    if (count($this->parts[self::HAVING])) {
      $q = $q.' HAVING ('.implode(') AND (', $this->parts[self::HAVING]).')';
    }

    // ORDER clause
    if (isset($this->parts[self::ORDER])) {
      $q = $q.' ORDER BY '.implode(',', $this->parts[self::ORDER]);
    }

    // LIMIT clause
    if (isset($this->parts[self::LIMIT_COUNT])) {
      $q = $q.' LIMIT ';
      if (isset($this->parts[self::LIMIT_OFFSET])) {
        $q = $q.$this->parts[self::LIMIT_OFFSET].', ';
      }
      $q = $q.$this->parts[self::LIMIT_COUNT];
    }

    return $q;
  }

  /**
   * Reset part of the query, or all parts if no argument is passed.
   *
   * Note: If you reset all parts then you need to use the ->columns() method to add
   * columns (usually it's easier to pass columns to the select() method of coreDatabase).
   *
   * @param $part Which part to reset (see class constants)
   *
   * @return self
   */
  public function reset($part = null)
  {
    if ($part === null) {
      $this->parts                = [];
      $this->parts[self::COLUMNS] = [];
      $this->parts[self::JOINS]   = [];
      $this->parts[self::WHERE]   = [];
      $this->parts[self::HAVING]  = [];
    } else {
      switch ($part) {
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
      }
    }

    return $this;
  }

  /**
   * Adds to the columns.
   *
   * @return self
   */
  public function columns(array|string $cols)
  {
    if (!is_array($cols)) {
      $cols = [$cols];
    }
    $this->parts[self::COLUMNS] = array_merge($this->parts[self::COLUMNS], $cols);

    return $this;
  }

  /**
   * Specify FROM table(s).
   *
   * Ex1:   'table1'
   * Ex2:   ['t' => 'table']
   * Ex3:   ['table1', 't2' => 'table2']
   *
   * @param array<string,string>|string $table One or more tables, use k => v for alias
   *
   * @return self
   */
  public function from(array|string $table)
  {
    $this->parts[self::FROM] = $table;

    return $this;
  }

  /**
   * @param array<string,string>|string $table     One or more tables, use k => v for alias
   * @param mixed                       $condition
   *
   * @return self
   */
  public function join(array|string $table, $condition)
  {
    $this->parts[self::JOINS][] = ' JOIN '.$this->db->aliases($table).' ON '.$condition;

    return $this;
  }

  /**
   * @param array<string,string>|string $table   The table to join (use k => v for alias)
   * @param array|string                $columns Column(s) for the USING clause
   *
   * @return self
   */
  public function joinUsing(array|string $table, array|string $columns)
  {
    $this->parts[self::JOINS][] = ' JOIN '.$this->db->aliases($table).' USING('.$this->db->aliases($columns).')';

    return $this;
  }

  /**
   * @param array<string,string>|string $table The table to join (use k => v for alias)
   *
   * @return self
   */
  public function joinLeft(array|string $table, string $condition)
  {
    $this->parts[self::JOINS][] = ' LEFT JOIN '.$this->db->aliases($table).' ON '.$condition;

    return $this;
  }

  /**
   * @param array<string,string>|string $table   The table to join (use k => v for alias)
   * @param array|string                $columns Column(s) for the USING clause
   *
   * @return self
   */
  public function joinLeftUsing(array|string $table, array|string $columns)
  {
    $this->parts[self::JOINS][] = ' LEFT JOIN '.$this->db->aliases($table).' USING('.$this->db->aliases($columns).')';

    return $this;
  }

  /**
   * @param mixed      $criteria
   * @param mixed|null $bindParams
   *
   * @return self
   */
  public function where($criteria, $bindParams = null)
  {
    if (func_num_args() > 2) {
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
   * @param int[]  $values
   *
   * @return self
   */
  public function whereIn($column, $values)
  {
    $this->parts[self::WHERE][] = $column.' IN ('.implode(',', $values).')';

    return $this;
  }

  /**
   * @param mixed $columns
   *
   * @return self
   */
  public function group($columns)
  {
    $this->parts[self::GROUP] = (array) $columns;

    return $this;
  }

  /**
   * @param mixed      $criteria
   * @param mixed|null $bindParams
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
   * @param mixed $columns
   *
   * @return self
   */
  public function order($columns)
  {
    $this->parts[self::ORDER] = (array) $columns;

    return $this;
  }

  /**
   * @param mixed      $numrows
   * @param mixed|null $offset
   *
   * @return self
   */
  public function limit($numrows, $offset = null)
  {
    $this->parts[self::LIMIT_COUNT]  = $numrows;
    $this->parts[self::LIMIT_OFFSET] = $offset;

    return $this;
  }

  /**
   * Convenient way to apply paging.
   *
   * @param int $pageNum     zero-based page number
   * @param int $rowsPerPage
   *
   * @return self
   */
  public function limitPage($pageNum, $rowsPerPage)
  {
    $this->parts[self::LIMIT_COUNT]  = $rowsPerPage;
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
   * @return bool True on success, or throws sfException
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
 */
abstract class coreDatabaseStatement
{
  protected $_adapter;

  public function __construct(coreDatabase $adapter, string $sql)
  {
    $this->_adapter = $adapter;
    $this->_prepare($sql);
  }

  abstract protected function _prepare(string $sql): void;

  abstract protected function _execute(?array $params): bool;

  /**
   * Executes a prepared statement.
   *
   * @param array $params OPTIONAL Values to bind to parameter placeholders
   *
   * @return bool TRUE on success or FALSE on failure
   */
  public function execute(?array $params = null)
  {
    return $this->_execute($params);
  }

  /**
   * Returns the number of rows affected by the last INSERT, UPDATE,
   * REPLACE or DELETE query.
   *
   * For SELECT statements mysqli_affected_rows() works like mysqli_num_rows()
   *
   * @return int the number of rows affected
   */
  abstract public function rowCount();
}
