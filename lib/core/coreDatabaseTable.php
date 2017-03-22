<?php
/**
 * coreDatabaseTable is a database table model to encapsulate logic
 * that accesses data of a particular table.
 * 
 * Provides a shortcut to coreDatabase ->select without specifying table.
 * 
 * Avoid hard coding a table name by using ->getName() method. The table name
 * is automatically guessed from the class name (eg. MyTablePeer).
 * 
 * insert() and update() also handle the setting and updating of timestamps.
 * 
 * 
 * 
 * 
 * @author     Fabrice Denis
 */

abstract class coreDatabaseTable
{
  /**
   * If a column is present with this name, it is assumd to be a TIMESTAMP
   * and will be set by the insert() method.
   * 
   * If new records are created without using insert(), you must take care to
   * assing the current timestamp because MySQL only auto updates one column
   * in the table.
   *
   * SQL => created_on TIMESTAMP NOT NULL DEFAULT 0
   * 
   * If you don't use the UPDATED_ON column, then CREATED_ON can use a default
   * value:
   * 
   * SQL => created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
     * 
   */
  const CREATED_ON = 'created_on';

  /**
   * If a column is present with this name, it is assumed tp be a TIMESTAMP
   * and will be set by the insert() and update() methods.
   * 
   * SQL => updated_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
   */
  const UPDATED_ON = 'updated_on';

  /**
   * coreDatabase reference is static for convenience in peer methods.
   */
  protected static
    $db        = null;

  /**
   * These should be redefined in the extending class.
   * 
   * Static variables can not be overridden so these are protected, and an instance
   * of the class is created on autoloading.
   * 
   * @var $tableName  Name of the table in the database (may be different than the class name).
   * @var $columns    An array of column names.
   */
  protected
    $tableName   = null,
    $columns     = null;

  /**
   * 
   * 
   * @return 
   * @param object $childclass
   */
  public static function _getInstance($childclass)
  {
    static $instances = array();
    if (!isset($instances[$childclass]))
    {
      $instance = new $childclass($childclass);
      $instances[$childclass] = $instance;
    }
    return $instances[$childclass];
  }

  /**
   * Initialize this table peer instance.
   * 
   * The only reason we instantiate the class is that static variables
   * can not be overriden in the extending class as of Php 5.2.
   * 
   * @param coreDatabase $db    coreDatabase instance.
   * @param string $tableName   Table name obtained from the class name without 'Peer' suffix
   * @return 
   */
  public function __construct($peerclass)
  {
    // static reference for convenience
    self::$db = sfProjectConfiguration::getActive()->getDatabase();
    
    // check naming of model and determine the default table name
    if (!preg_match('/^(\\w+)Peer$/', $peerclass, $matches))
    {
      throw new sfException('Invalid coreDatabaseTable class name: '.$peerclass);
    }
      
    // if not explicitly set, the table name is derived from the class name, in lowercase
    if ($this->tableName === null)
    {
      $this->tableName = strtolower($matches[1]);
    }

    // columns check
    if (!is_array($this->columns)) {
      throw new sfException('coreDatabaseTable table '.$this->getName().' columns not set.');
    }

    $this->initialize();
  }

  /**
   * If application-specific logic needs to be initialized when a table class is instanced,
   * override this function.
   *
   */
  public function initialize()
  {
  }

  /**
   * Return name of table in the database.
   *
   */
  public function getName()
  {
    return $this->tableName;
  }

  /**
   * Return name of table in the database.
   *
   */
  public function getColumns()
  {
    return $this->columns;
  }

  /**
   * Build a select query using this table as FROM clause.
   * 
   * @return object  coreDatabaseSelect instance (automatically converts to string).
   */
  public function select($columns = null)
  {
    return self::$db->select($columns)->from($this->getName());
  }

  /**
   * Return count of rows in table
   *
   * @return mixed  Number of rows, or FALSE on failure.
   */
  public function count($where = null, $bindParams = null)
  {
    $stmt = self::$db->select('count(*)')->from($this->getName());
    if ($where!==null) {
      $stmt = $stmt->where($where, $bindParams);
    }
    return self::$db->fetchOne($stmt);    
  }

  /**
   * Insert a new row with specified key => values,
   * ignored columns get default values as per SQL TABLE creation.
   * 
   * This method updates the CREATED_ON and UPDATED_ON timestamps if present.
   *
   * @param  array   An associative array of properties (column names) and data.
   * 
   * @return boolean TRUE on success, FALSE on error.
   */
  public function insert($data = array())
  {
    // creation timestamp
    if (in_array(self::CREATED_ON, $this->getColumns()) && !isset($data[self::CREATED_ON]))
    {
      $data[self::CREATED_ON] = new coreDbExpr('NOW()');
    }
    
    // update timestamp
    if (in_array(self::UPDATED_ON, $this->getColumns()) && !isset($data[self::UPDATED_ON]))
    {
      $data[self::UPDATED_ON] = new coreDbExpr('NOW()');
    }

    return self::$db->insert($this->getName(), $data);
  }

  /**
   * Updates columns (key => values) in matching row(s) with optional where clause.
   * 
   * Automatically update the UPDATED_ON timestamp if present.
   *
   * @param  array   An associative array of properties (column names) and data.
   * @param  string  Where clause with optional '?' quoted parameters.
   * @param  mixed   Single value or array of values for quoted parameters.
   * 
   * @return boolean TRUE on success, FALSE on error.
   */
  public function update(array $data, $where = null, $bindParams = null)
  {
    // update timestamp
    if (in_array(self::UPDATED_ON, $this->getColumns())
      && !isset($data[self::UPDATED_ON]))
    {
      $data[self::UPDATED_ON] = new coreDbExpr('NOW()');
    }

    return self::$db->update($this->getName(), $data, $where, $bindParams);
  }

  
  /**
   * Update or insert a new column (key => values). Does not use MySQL-only
   * replace statement. This is a shortcut for the insert() and update()
   * methods.
   * 
   *

   * @param  array   An associative array of properties (column names) and data.
   * @param  string  Where clause with optional '?' quoted parameters.
   * @param  mixed   Single value or array of values for quoted parameters.
   * 
   * @return boolean TRUE on success, FALSE on error.
   */
  public function replace(array $data, array $primaryKeys)
  {
    $where = self::$db->chain($primaryKeys, ' AND ');

    // row exists, update
    if ($this->count($where))
    {
      return $this->update($data, $where);
    }
    
    // row does not exist, add the primary key values in the $data
    foreach ($primaryKeys as $colName => $colValue)
    {
      $data[$colName] = $colValue;
    }

    return $this->insert($data);
  }

  /**
   * Delete all rows, or matching rows with optional where clause.
   *
   * @param  string  Where clause with optional '?' quoted parameters.
   * @param  mixed   Single value or array of values for quoted parameters.
   * 
   * @return boolean TRUE on success, FALSE on error.
   */
  public function delete($where = null, $bindParams = null)
  {
    return self::$db->delete($this->getName(), $where, $bindParams);
  }
}
